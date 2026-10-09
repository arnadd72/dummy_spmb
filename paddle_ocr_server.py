"""
paddle_ocr_server.py  ←  SELF-HOSTED OCR SERVICE
Engine  : EasyOCR 1.7.2  (Python 3.14 compatible, GPU-optional)
Tujuan  : Menggantikan OCR cloud. Semua pemrosesan di mesin lokal.
Arsitektur: FastAPI → EasyOCR → Layout Engine → KK/Akta Structured Parser
           → Validation + Confidence → JSON Response

Prinsip: ACCURACY OVER AUTOFILL
         - Tidak pernah menebak data
         - Setiap field punya confidence (HIGH / MEDIUM / LOW / NOT_FOUND)
         - Jika tidak yakin → kembalikan NOT_FOUND, kosongkan field
"""

from __future__ import annotations
import io
import re
import math
import traceback
from typing import Optional

import cv2
import numpy as np
from fastapi import FastAPI, File, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse

# ─── Lazy-load EasyOCR so the server starts fast ──────────────────────────────
_reader = None
def get_reader():
    global _reader
    if _reader is None:
        import easyocr
        print("[OCR] Loading EasyOCR model (first load only)…")
        _reader = easyocr.Reader(
            ['id', 'en'],
            gpu=False,          # CPU-safe, works without CUDA
            verbose=False,
        )
        print("[OCR] Model ready.")
    return _reader


# ─── FastAPI App ───────────────────────────────────────────────────────────────
app = FastAPI(title="Self-Hosted OCR Service", version="2.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)


# ══════════════════════════════════════════════════════════════════════════════
# IMAGE PREPROCESSING
# ══════════════════════════════════════════════════════════════════════════════

def preprocess(img: np.ndarray) -> np.ndarray:
    """
    Agresif preprocessing untuk memaksimalkan keterbacaan teks kecil
    pada dokumen seperti KK dan Akta Kelahiran yang sering buram/gelap.
    """
    h, w = img.shape[:2]

    # 1) Upscale jika terlalu kecil (target minimum sisi terpanjang 2000px)
    longest = max(h, w)
    if longest < 2000:
        scale = 2000.0 / longest
        img = cv2.resize(img, None, fx=scale, fy=scale,
                         interpolation=cv2.INTER_CUBIC)

    # 2) Grayscale
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    # 3) Auto-rotate correction (deskew) berdasarkan entropy—skip jika < 0.5°
    # (simple: cari sudut garis horizontal via Hough)
    # Kita skip deskew complex karena easyocr sudah handle rotasi

    # 4) CLAHE (Contrast Limited Adaptive Histogram Equalization)
    #    Bagus untuk dokumen dengan pencahayaan tidak merata (foto dengan flash, bayangan)
    clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)

    # 5) Gaussian denoise ringan
    denoised = cv2.GaussianBlur(enhanced, (3, 3), 0)

    # 6) Adaptive threshold — bagus untuk background bergaris/berwarna
    binary = cv2.adaptiveThreshold(
        denoised, 255,
        cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY,
        blockSize=25,
        C=8
    )

    # 7) Morfologi: hilangkan noise titik kecil, perjelas kontur huruf
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (2, 2))
    clean = cv2.morphologyEx(binary, cv2.MORPH_CLOSE, kernel)

    # Kembalikan ke BGR agar EasyOCR happy
    return cv2.cvtColor(clean, cv2.COLOR_GRAY2BGR)


def preprocess_raw(img: np.ndarray) -> np.ndarray:
    """
    Preprocessing ringan untuk gambar yang sudah cukup jelas.
    Hanya upscale + enhance kontras tanpa threshold agresif.
    """
    h, w = img.shape[:2]
    longest = max(h, w)
    if longest < 2000:
        scale = 2000.0 / longest
        img = cv2.resize(img, None, fx=scale, fy=scale,
                         interpolation=cv2.INTER_CUBIC)

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    clahe = cv2.createCLAHE(clipLimit=1.5, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)
    return cv2.cvtColor(enhanced, cv2.COLOR_GRAY2BGR)


# ══════════════════════════════════════════════════════════════════════════════
# LAYOUT ENGINE — Bounding Box Analysis
# ══════════════════════════════════════════════════════════════════════════════

class Word:
    """Represents a single OCR-detected word with spatial info."""
    __slots__ = ('text', 'conf', 'x0', 'y0', 'x1', 'y1', 'cx', 'cy', 'h')

    def __init__(self, text: str, conf: float, bbox):
        self.text = text.strip()
        self.conf = conf  # 0..1
        xs = [p[0] for p in bbox]
        ys = [p[1] for p in bbox]
        self.x0 = float(min(xs))
        self.y0 = float(min(ys))
        self.x1 = float(max(xs))
        self.y1 = float(max(ys))
        self.cx = (self.x0 + self.x1) / 2
        self.cy = (self.y0 + self.y1) / 2
        self.h  = self.y1 - self.y0

    def __repr__(self):
        return f"Word({self.text!r}, conf={self.conf:.2f}, y={self.cy:.0f})"


class Line:
    """A horizontal group of Words on the same row."""
    def __init__(self, words: list[Word]):
        self.words = sorted(words, key=lambda w: w.x0)
        self.y0 = min(w.y0 for w in words)
        self.y1 = max(w.y1 for w in words)
        self.cy = sum(w.cy for w in words) / len(words)
        self.x0 = min(w.x0 for w in words)
        self.x1 = max(w.x1 for w in words)

    @property
    def text(self) -> str:
        return ' '.join(w.text for w in self.words)

    @property
    def avg_conf(self) -> float:
        return sum(w.conf for w in self.words) / len(self.words)

    def word_at_x(self, x_min: float, x_max: float) -> list[Word]:
        """Get words within an x-column range."""
        return [w for w in self.words if w.cx >= x_min and w.cx <= x_max]

    def __repr__(self):
        return f"Line(y={self.cy:.0f}, {self.text!r})"


def group_into_lines(words: list[Word], tol_factor: float = 0.6) -> list[Line]:
    """
    Group words into horizontal lines using Y-coordinate proximity.
    tol_factor: fraction of median word height to use as row tolerance.
    """
    if not words:
        return []

    heights = [w.h for w in words if w.h > 2]
    median_h = sorted(heights)[len(heights) // 2] if heights else 15
    tol = max(median_h * tol_factor, 8)

    sorted_words = sorted(words, key=lambda w: w.cy)
    lines: list[list[Word]] = []
    current: list[Word] = [sorted_words[0]]

    for w in sorted_words[1:]:
        if abs(w.cy - current[-1].cy) <= tol:
            current.append(w)
        else:
            lines.append(current)
            current = [w]
    if current:
        lines.append(current)

    return [Line(g) for g in lines]


def run_easyocr(img: np.ndarray) -> list[Word]:
    """Run EasyOCR and return list of Word objects."""
    reader = get_reader()
    results = reader.readtext(
        img,
        detail=1,
        paragraph=False,
        width_ths=0.7,
        height_ths=0.5,
        decoder='greedy',
        batch_size=1,
        workers=0,
    )
    words = []
    for (bbox, text, prob) in results:
        t = text.strip()
        if not t or prob < 0.15:
            continue
        words.append(Word(t, prob, bbox))
    return words


def ocr_with_fallback(img_raw: np.ndarray) -> list[Word]:
    """
    Run OCR twice (preprocessed + raw), merge results, deduplicate by position.
    Mengambil yang terbaik dari dua preprocessing.
    """
    img_pre = preprocess(img_raw)
    img_light = preprocess_raw(img_raw)

    words_pre   = run_easyocr(img_pre)
    words_light = run_easyocr(img_light)

    # Merge: prefer higher-confidence word when two words overlap spatially
    all_words = words_pre[:]
    for w2 in words_light:
        overlap = False
        for w1 in words_pre:
            if _iou(w1, w2) > 0.3:
                overlap = True
                if w2.conf > w1.conf:
                    w1.text = w2.text
                    w1.conf = w2.conf
                break
        if not overlap:
            all_words.append(w2)

    return all_words


def _iou(a: Word, b: Word) -> float:
    xi0 = max(a.x0, b.x0); yi0 = max(a.y0, b.y0)
    xi1 = min(a.x1, b.x1); yi1 = min(a.y1, b.y1)
    inter = max(0, xi1 - xi0) * max(0, yi1 - yi0)
    if inter == 0:
        return 0.0
    ua = (a.x1-a.x0)*(a.y1-a.y0)
    ub = (b.x1-b.x0)*(b.y1-b.y0)
    return inter / (ua + ub - inter)


# ══════════════════════════════════════════════════════════════════════════════
# TEXT NORMALIZATION
# ══════════════════════════════════════════════════════════════════════════════

# Common OCR character substitution mistakes
_CHAR_FIXES = str.maketrans({
    '|': 'I', '\u0131': 'i', '\ufb01': 'fi', '\ufb02': 'fl',
    '\u2019': "'", '\u201c': '"', '\u201d': '"',
})

_WORD_FIXES = {
    r'\b0(?=[A-Z])': 'O',       # 0KTOBER → Oktober
    r'(?<=[A-Z])0\b': 'O',     # Menurut0 → Menurut
    r'\bIl(?=DON|ndon)': 'Indo',
    r'\bIlDONESI[Aa]\b': 'INDONESIA',
    r'\bKacupere[nh]\b': 'Kabupaten',
    r'\bmcnurut\b': 'menurut',
    r'\bsuanl[\-\s]?[Ll]eteri\b': 'suami-isteri',
    r'\bsuanl\b': 'suami',
    r'\b[Ll]eteri\b': 'isteri',
    r'\bBepilarpulun\b': 'Tiga puluh',
    r'\b\?r(\d+)': r'2\1',     # ?r2puluk → 22puluk
}

def normalize_text(text: str) -> str:
    t = text.translate(_CHAR_FIXES)
    for pat, repl in _WORD_FIXES.items():
        t = re.sub(pat, repl, t, flags=re.IGNORECASE)
    return t

def normalize_word(w: Word) -> Word:
    w.text = normalize_text(w.text)
    return w


# ══════════════════════════════════════════════════════════════════════════════
# FIELD BUILDER  (Confidence System)
# ══════════════════════════════════════════════════════════════════════════════

CONF_HIGH   = 'HIGH'
CONF_MEDIUM = 'MEDIUM'
CONF_LOW    = 'LOW'
CONF_NF     = 'NOT_FOUND'

def field(value='', confidence=CONF_NF, source='', note='') -> dict:
    return {
        'value': str(value or '').strip(),
        'confidence': confidence,
        'source': source,
        'note': note,
    }

def conf_from_ocr(avg_conf: float) -> str:
    if avg_conf >= 0.85: return CONF_HIGH
    if avg_conf >= 0.65: return CONF_MEDIUM
    if avg_conf >= 0.40: return CONF_LOW
    return CONF_NF


# ══════════════════════════════════════════════════════════════════════════════
# VALIDATION HELPERS
# ══════════════════════════════════════════════════════════════════════════════

_NIK_CHARS = str.maketrans('OoIlSB', '001158')

def normalize_nik(s: str) -> str:
    return re.sub(r'\D', '', s.translate(_NIK_CHARS))

def validate_nik(s: str) -> Optional[str]:
    """Returns 16-digit NIK string or None."""
    n = normalize_nik(str(s or ''))
    if re.match(r'^[1-9]\d{15}$', n):
        return n
    return None

def validate_date(day: int, month: int, year: int) -> Optional[str]:
    """Returns 'YYYY-MM-DD' or None."""
    if not (1 <= day <= 31 and 1 <= month <= 12 and 1900 <= year <= 2025):
        return None
    return f"{year:04d}-{month:02d}-{day:02d}"

def is_plausible_name(s: str) -> bool:
    """A name must be at least 2 words OR a single word ≥ 4 chars, all alphabetic."""
    if not s:
        return False
    words = [w for w in s.split() if re.search(r'[a-zA-Z]', w)]
    if len(words) == 0:
        return False
    if len(words) == 1 and len(words[0]) < 4:
        return False
    # Reject if too many digits or special chars
    alpha_ratio = sum(c.isalpha() or c.isspace() for c in s) / max(len(s), 1)
    return alpha_ratio >= 0.8

_NON_NAME = re.compile(
    r'^(dan|atau|yang|dari|pada|dengan|oleh|bahwa|telah|seorang|anak|laki|'
    r'perempuan|wanita|pria|lahir|kelahiran|sipil|akta|kutipan|catatan|'
    r'pencatatan|sesuai|daftar|nomor|no|tanggal|hari|tahun|bulan|di|ke|'
    r'dalam|untuk|ini|tersebut|adalah|kota|kabupaten|provinsi|indonesia|'
    r'republik|negara|penduduk|kependudukan|dinas|kantor|camat|lurah|'
    r'bupati|walikota|gubernur|menteri|rt|rw|desa|kelurahan|kecamatan|'
    r'kepala|keluarga|suami|isteri|istri|ibu|ayah|anak|cucu|orang|tua)$',
    re.IGNORECASE
)

def clean_name(s: str) -> str:
    # Remove dashes, underscores, and excessive punctuation often used as fillers
    s = re.sub(r'[\-_=]+', ' ', s)
    s = re.sub(r'[^a-zA-Z\s\.]', ' ', s)
    tokens = [w for w in re.split(r'\s+', s.strip()) if w]
    tokens = [t for t in tokens if len(t) >= 2
              and not _NON_NAME.match(t)
              and re.search(r'[a-zA-Z]', t)]
    return ' '.join(tokens).strip()

def title_name(s: str) -> str:
    return ' '.join(w.capitalize() for w in s.split())


# ══════════════════════════════════════════════════════════════════════════════
# DATE PARSER — Month map + kata angka
# ══════════════════════════════════════════════════════════════════════════════

MONTH_MAP = {
    'januari': 1, 'jan': 1,
    'februari': 2, 'pebruari': 2, 'feb': 2, 'febuari': 2,
    'maret': 3, 'mar': 3, 'mare': 3,
    'april': 4, 'apr': 4,
    'mei': 5, 'may': 5,
    'juni': 6, 'jun': 6,
    'juli': 7, 'jul': 7,
    'agustus': 8, 'agust': 8, 'agt': 8, 'agus': 8, 'agustis': 8,
    'september': 9, 'sept': 9, 'sep': 9, 'septembar': 9,
    'oktober': 10, 'okt': 10, 'oktobar': 10, 'oct': 10, 'oktoher': 10,
    'november': 11, 'nov': 11, 'nopember': 11,
    'desember': 12, 'des': 12, 'dec': 12, 'desembar': 12,
}

KATA_SATUAN = {
    'satu':1,'dua':2,'tiga':3,'empat':4,'lima':5,'enam':6,
    'tujuh':7,'delapan':8,'sembilan':9,'sepuluh':10,'sebelas':11,
}
KATA_PULUHAN = {
    'dua':20,'tiga':30,'empat':40,'lima':50,'enam':60,
    'tujuh':70,'delapan':80,'sembilan':90,
}
KATA_RATUS = {'satu':100,'dua':200,'tiga':300,'empat':400,'lima':500,
              'enam':600,'tujuh':700,'delapan':800,'sembilan':900}

def parse_kata_angka(s: str) -> Optional[int]:
    """Parse Indonesian number words → integer. Returns None on failure."""
    s = s.lower().strip()
    # Direct digits
    if re.match(r'^\d+$', s):
        return int(s)
    total = 0
    tokens = re.split(r'[\s\-]+', s)
    i = 0
    while i < len(tokens):
        w = tokens[i]
        if w in ('seribu', 'satu') and i+1 < len(tokens) and tokens[i+1] == 'ribu':
            total += 1000; i += 2 if w == 'satu' else 1; continue
        if w == 'dua' and i+1 < len(tokens) and tokens[i+1] == 'ribu':
            total += 2000; i += 2; continue
        if w in KATA_RATUS and i+1 < len(tokens) and tokens[i+1] == 'ratus':
            total += KATA_RATUS[w]; i += 2; continue
        if w == 'se' and i+1 < len(tokens) and tokens[i+1] == 'ratus':
            total += 100; i += 2; continue
        if w in KATA_PULUHAN and i+1 < len(tokens) and tokens[i+1] == 'puluh':
            total += KATA_PULUHAN[w]; i += 2
            if i < len(tokens) and tokens[i] in KATA_SATUAN:
                total += KATA_SATUAN[tokens[i]]; i += 1
            continue
        if w == 'sepuluh': total += 10; i += 1; continue
        if w == 'sebelas': total += 11; i += 1; continue
        if w in KATA_SATUAN: total += KATA_SATUAN[w]; i += 1; continue
        i += 1
    return total or None


MONTH_PAT = '|'.join(sorted(MONTH_MAP.keys(), key=len, reverse=True))

def extract_dates_with_context(text: str) -> list[dict]:
    """
    Extract ALL dates from text with context about what precedes them.
    Returns list of {raw, day, month, year, formatted, before, type}.
    """
    results = []

    # 1) Numeric: DD/MM/YYYY or DD-MM-YYYY
    for m in re.finditer(r'\b(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})\b', text):
        d, mo, y = int(m[1]), int(m[2]), int(m[3])
        fmt = validate_date(d, mo, y)
        if fmt:
            before = text[max(0, m.start()-120):m.start()].lower()
            results.append({'raw': m[0], 'day': d, 'month': mo, 'year': y,
                            'formatted': fmt, 'before': before, 'type': 'numeric'})

    # 2) Named month: "15 Oktober 1998"
    pat2 = re.compile(r'\b(\d{1,2})\s+(' + MONTH_PAT + r')\s+(\d{4})\b', re.IGNORECASE)
    for m in pat2.finditer(text):
        d = int(m[1])
        mo = MONTH_MAP.get(m[2].lower(), 0)
        y = int(m[3])
        fmt = validate_date(d, mo, y)
        if fmt:
            before = text[max(0, m.start()-120):m.start()].lower()
            results.append({'raw': m[0], 'day': d, 'month': mo, 'year': y,
                            'formatted': fmt, 'before': before, 'type': 'named'})

    # 3) Kata angka: "lima belas Oktober seribu sembilan ratus sembilan delapan"
    pat3 = re.compile(
        r'([a-z]+(?:\s+[a-z]+){0,5})\s+(' + MONTH_PAT + r')\s+([a-z]+(?:\s+[a-z]+){1,8})',
        re.IGNORECASE
    )
    for m in pat3.finditer(text):
        day_n  = parse_kata_angka(m[1])
        mo_n   = MONTH_MAP.get(m[2].lower(), 0)
        year_n = parse_kata_angka(m[3])
        if day_n and mo_n and year_n:
            fmt = validate_date(day_n, mo_n, year_n)
            if fmt:
                before = text[max(0, m.start()-120):m.start()].lower()
                dup = any(r['formatted'] == fmt for r in results)
                if not dup:
                    results.append({'raw': m[0], 'day': day_n, 'month': mo_n,
                                    'year': year_n, 'formatted': fmt,
                                    'before': before, 'type': 'kata'})
    return results


_BIRTH_KEYWORDS  = re.compile(r'lahir|dilahirkan|tanggal\s*lahir|telah\s*lahir|pada\s*tanggal', re.I)
_ISSUE_KEYWORDS  = re.compile(r'penerbit|pencatat|diterbitkan|dicatat|register|pengeluaran|dikeluarkan|salinan|kutipan|tanggal\s*(?:pencatatan|pengeluaran|pembuatan)', re.I)

def pick_birth_date(dates: list[dict]) -> dict:
    """
    Pick the birth date from multiple candidates.
    Strategy:
    1. If 'lahir' appears in context → HIGH confidence
    2. If 'penerbit/dicatat' in context → skip (issue date, not birth)
    3. If only 1 date → MEDIUM (could be birth or issue)
    4. Score by year (children: 2004-2016 for SMA/SMK enrollment)
    """
    if not dates:
        return field()

    for d in dates:
        has_birth  = bool(_BIRTH_KEYWORDS.search(d['before']))
        has_issue  = bool(_ISSUE_KEYWORDS.search(d['before']))
        d['score'] = 0
        if has_birth:  d['score'] += 10
        if has_issue:  d['score'] -= 8
        # Year scoring for school-age children
        y = d['year']
        if 2004 <= y <= 2016: d['score'] += 4
        elif 2000 <= y < 2004: d['score'] += 2
        elif y < 1980: d['score'] -= 3  # unlikely birth year for a child
        if d['type'] == 'kata':   d['score'] += 2
        if d['type'] == 'named':  d['score'] += 1

    candidates = sorted(dates, key=lambda x: x['score'], reverse=True)
    best = candidates[0]

    # Reject if clearly an issue/registration date
    if best['score'] <= -5:
        return field(note='Hanya tanggal dokumen, bukan lahir')

    if len(candidates) == 1:
        if best['score'] >= 10:
            return field(best['formatted'], CONF_HIGH, 'ocr', f"score={best['score']}")
        return field(best['formatted'], CONF_MEDIUM, 'ocr', f"1 tanggal, score={best['score']}")

    second = candidates[1]
    if best['score'] >= 10 and best['score'] - second['score'] >= 5:
        return field(best['formatted'], CONF_HIGH, 'ocr', f"score={best['score']}")
    if best['score'] > 0:
        return field(best['formatted'], CONF_MEDIUM, 'ocr', f"ambig, score={best['score']}")
    return field(note='Tidak ada tanggal lahir yang jelas')


# ══════════════════════════════════════════════════════════════════════════════
# AKTA KELAHIRAN PARSER — Layout-Aware
# ══════════════════════════════════════════════════════════════════════════════

def parse_akta(words: list[Word], lines: list[Line]) -> dict:
    """
    Parse Akta Kelahiran using bounding box layout + label context.
    Returns structured dict with confidence for each field.
    """
    # Full normalized text for regex
    full_text = '\n'.join(normalize_text(l.text) for l in lines)

    result = {
        'nama':          field(),
        'tempat_lahir':  field(),
        'tanggal_lahir': field(),
        'jenis_kelamin': field(),
        'nama_ayah':     field(),
        'nama_ibu':      field(),
    }

    # ── 1. JENIS KELAMIN ────────────────────────────────────────────────────
    # Patterns ordered most-specific to least. We do NOT fall back to target name.
    jk_patterns = [
        (re.compile(r'seorang\s+anak\s+(laki[\-\s]?laki|perempuan|pria|wanita)', re.I), 1),
        (re.compile(r'\(anak\s+(?:ke\w+\s+)?(laki[\-\s]?laki|perempuan)\)', re.I), 1),
        (re.compile(r'anak\s+(laki[\-\s]?laki|perempuan|pria|wanita)', re.I), 1),
        (re.compile(r'jenis\s+kelamin\s*[:\-]?\s*(laki[\-\s]?laki|perempuan|pria|wanita)', re.I), 1),
        (re.compile(r'berjenis\s+kelamin\s+(laki[\-\s]?laki|perempuan)', re.I), 1),
        # EasyOCR noise patterns
        (re.compile(r'en[kh]\s+pere?n?pue?n', re.I), 0),   # group 0 = full match → P
        (re.compile(r'en[kh]\s+laki', re.I), 0),
    ]
    for pat, grp in jk_patterns:
        m = pat.search(full_text)
        if m:
            raw_val = m.group(grp) if grp else m.group(0)
            is_female = bool(re.search(r'perempuan|wanita|pue?n', raw_val, re.I))
            jk = 'P' if is_female else 'L'
            result['jenis_kelamin'] = field(jk, CONF_HIGH, 'layout',
                                             f'matched: {m.group(0)[:40]}')
            break

    # ── 2. NAMA ANAK ────────────────────────────────────────────────────────
    # We use multiple strategies but NEVER use targetStudentName as fallback.
    nama_found = False

    # Strategy A: Explicit label "bernama" or "nama anak/lengkap"
    for pat in [
        re.compile(r'(?:yang\s+)?bernama\s*[:\-]?\s*([A-Za-z][A-Za-z\s\.]{2,60}?)(?=\s*[\(\,\.\n]|\s*anak|$)', re.I),
        re.compile(r'nama\s+(?:anak|lengkap|bayi)?\s*[:\-]\s*([A-Za-z][A-Za-z\s\.]{2,60}?)(?=\s*[\(\,\.\n]|$)', re.I),
        re.compile(r'lahir\s*:\s*([A-Za-z][A-Za-z\s\.]{2,60}?)(?=\s*[\(\,\.\n]|\s*anak|$)', re.I),
    ]:
        m = pat.search(full_text)
        if m:
            c = clean_name(m.group(1))
            if len(c) >= 3 and not re.search(r'kutipan|akta|kelahiran|pencatatan|sipil|indonesia', c, re.I):
                result['nama'] = field(title_name(c), CONF_HIGH, 'label', f'pattern: {pat.pattern[:30]}')
                nama_found = True
                break

    # Strategy B: Line immediately before "(anak ke... / anak laki/perempuan)"
    if not nama_found:
        for i, ln in enumerate(lines):
            if re.search(r'\(\s*anak|\banak\s+ke', ln.text, re.I) and i > 0:
                prev = clean_name(normalize_text(lines[i-1].text))
                if len(prev) >= 3 and not re.search(r'akta|kelahiran|warga|negara|kutipan', prev, re.I):
                    avg_c = lines[i-1].avg_conf
                    result['nama'] = field(title_name(prev), conf_from_ocr(avg_c),
                                           'layout', f'line before (anak), conf={avg_c:.2f}')
                    nama_found = True
                    break

    # Strategy C: Line after standalone "lahir:" or "telah lahir:"
    if not nama_found:
        for i, ln in enumerate(lines):
            if re.search(r'\blahir\s*[:\-]*\s*$', ln.text, re.I) and i+1 < len(lines):
                nxt = clean_name(normalize_text(lines[i+1].text))
                if len(nxt) >= 3 and is_plausible_name(nxt) \
                   and not re.match(r'^(dari|pada|tahun|di\b|seribu)', nxt, re.I):
                    result['nama'] = field(title_name(nxt), CONF_HIGH, 'layout',
                                           'line after lahir:')
                    nama_found = True
                    break

    # Strategy D: Scan all lines for name-like text after "lahir" keyword context
    if not nama_found:
        after_lahir = False
        for ln in lines:
            t = normalize_text(ln.text)
            if re.search(r'\blahir\b|\bdilahirkan\b', t, re.I):
                after_lahir = True
                continue
            if after_lahir:
                c = clean_name(t)
                if is_plausible_name(c) and len(c.split()) >= 2:
                    # Must NOT be a date or admin line
                    if not re.search(r'\d{4}|tanggal|tempat|tahun|bulan|hari', t, re.I):
                        result['nama'] = field(title_name(c), CONF_MEDIUM, 'layout',
                                               'after lahir context')
                        nama_found = True
                        break

    # ── 3. TEMPAT LAHIR ─────────────────────────────────────────────────────
    tp_patterns = [
        re.compile(r'tempat\s+(?:dan\s+tanggal\s+)?lahir\s*[:\-]?\s*([A-Za-z][A-Za-z\s\.]{2,35}?)(?=[,\.\n\/]|\s+tanggal|\s+pada)', re.I),
        re.compile(r'lahir\s+di\s+([A-Za-z][A-Za-z\s\.]{2,35}?)(?=[,\.\n]|\s+pada|\s+tanggal)', re.I),
        re.compile(r'bahwa\s+(?:ternyata\s+)?di\s+([A-Za-z][A-Za-z\s\.]{2,35}?)(?=[,\.\n]|\s+pada)', re.I),
        re.compile(r'(?:kabupaten|kab\.|kotamadya|kota|kacuperen)\s+([A-Za-z][A-Za-z\s\.]{2,30}?)(?=[,\.\n]|\s+pada|\s+ternyata)', re.I),
        # Fallback for multi-line: "di Kabupaten Simalungun \n Rajamaligas"
        re.compile(r'di\s*(?:kabupaten|kab\.|kota)\s*[A-Za-z]+\s*\n\s*([A-Za-z][A-Za-z\s\.]{2,30}?)(?=[,\.\n]|\s+pada)', re.I),
        re.compile(r'di\.?\s*([A-Za-z][A-Za-z\s]{2,25})(?=\s*bulan|\s*pada|\s*tahun|,)', re.I),
    ]
    for pat in tp_patterns:
        m = pat.search(full_text)
        if m and m.group(1):
            tv = title_name(m.group(1).strip())
            if len(tv) >= 3 and not re.search(r'daftar|tahun|kelahiran|kutipan|akta', tv, re.I):
                result['tempat_lahir'] = field(tv, CONF_HIGH, 'label',
                                                f'pattern: {pat.pattern[:30]}')
                break

    # ── 4. TANGGAL LAHIR ────────────────────────────────────────────────────
    all_dates = extract_dates_with_context(full_text)
    result['tanggal_lahir'] = pick_birth_date(all_dates)

    # ── 5. ORANG TUA ────────────────────────────────────────────────────────
    parent_patterns = [
        re.compile(r'suami[\-\s]+(?:is?te?r?i|leteri|lsieri)\s*[:\.]?\s*([A-Za-z][A-Za-z\s\.]{2,50})\s+dan\s+([A-Za-z][A-Za-z\s\.]{2,50})(?=[,\.\n]|$)', re.I),
        re.compile(r'pasangan\s+suami\s+is?te?r?i\s*[:\.]?\s*([A-Za-z][A-Za-z\s\.]{2,50})\s+dan\s+([A-Za-z][A-Za-z\s\.]{2,50})(?=[,\.\n]|$)', re.I),
    ]
    parent_found = False
    for pat in parent_patterns:
        m = pat.search(full_text)
        if m:
            ay = clean_name(m.group(1)); ib = clean_name(m.group(2))
            if is_plausible_name(ay):
                result['nama_ayah'] = field(title_name(ay), CONF_HIGH, 'label', 'pasangan')
            if is_plausible_name(ib):
                result['nama_ibu']  = field(title_name(ib), CONF_HIGH, 'label', 'pasangan')
            parent_found = True
            break

    if not parent_found:
        m_ay = re.search(r'(?:\bayah\b|\bbapak\b|\bsuami\b)\s*[:\-]\s*([A-Za-z][A-Za-z\s\.]{2,50})(?=[,\.\n])', full_text, re.I)
        if m_ay:
            ay = clean_name(m_ay.group(1))
            if is_plausible_name(ay):
                result['nama_ayah'] = field(title_name(ay), CONF_MEDIUM, 'label', 'ayah-label')

        m_ib = re.search(r'(?:\bibu\b|\bummi\b|\bisteri\b)\s*[:\-]\s*([A-Za-z][A-Za-z\s\.]{2,50})(?=[,\.\n])', full_text, re.I)
        if m_ib:
            ib = clean_name(m_ib.group(1))
            if is_plausible_name(ib):
                result['nama_ibu'] = field(title_name(ib), CONF_MEDIUM, 'label', 'ibu-label')

    return result


# ══════════════════════════════════════════════════════════════════════════════
# KARTU KELUARGA PARSER — Table Layout-Aware
# ══════════════════════════════════════════════════════════════════════════════

def parse_kk(words: list[Word], lines: list[Line]) -> dict:
    """
    Parse Kartu Keluarga using bounding-box column detection.
    KK adalah tabel: setiap anggota keluarga berada di satu baris (row).
    NIK harus datang dari baris yang SAMA dengan nama anggota.

    Strategy:
    1. Temukan semua NIK 16-digit beserta posisi Y mereka
    2. Untuk setiap NIK, temukan baris yang sama (Y tolerance)
    3. Dalam baris yang sama, cari nama, hubungan keluarga
    4. Bedakan Nomor KK (biasanya di header) dari NIK anggota
    5. Pilih anggota yang cocok dengan target name (untuk validasi saja)
    """
    full_text = '\n'.join(normalize_text(l.text) for l in lines)

    result = {
        'no_kk':       field(),
        'members':     [],
        'matched_nik': field(),
        'alamat':      field(),
        'nama_ayah':   field(),
        'nama_ibu':    field(),
        'has_nik':     False,
        'needs_manual_selection': False,
    }

    # ── 1. NOMOR KK (header dokumen, bukan NIK anggota) ─────────────────────
    # Nomor KK biasanya di atas, dalam konteks label "Nomor Kartu Keluarga"
    no_kk_val = None
    kk_label_patterns = [
        re.compile(r'(?:no(?:mor)?\s*(?:\.|\s)\s*kartu\s*keluarga|nomor\s*kk|no\.\s*kk)\s*[:\-]?\s*(\d[\d\s]{13,17})', re.I),
    ]
    for pat in kk_label_patterns:
        m = pat.search(full_text)
        if m:
            candidate = validate_nik(m.group(1).replace(' ', ''))
            if candidate:
                no_kk_val = candidate
                result['no_kk'] = field(candidate, CONF_HIGH, 'label', 'header KK')
                break

    # Fallback: Nomor KK sering ada di baris pertama yang mengandung 16 digit
    if not no_kk_val:
        for ln in lines[:8]:  # top 8 lines
            for w in ln.words:
                n = normalize_nik(w.text)
                if re.match(r'^[1-9]\d{15}$', n):
                    # Check if this line has KK-related label nearby
                    ctx = ' '.join(ww.text for ww in ln.words).lower()
                    if re.search(r'kartu|keluarga|kk|nomor|no', ctx):
                        no_kk_val = n
                        result['no_kk'] = field(n, CONF_HIGH, 'layout', 'header line')
                        break
            if no_kk_val:
                break

    # ── 2. ALAMAT ────────────────────────────────────────────────────────────
    addr_parts = []
    # RT/RW
    m_rtrw = re.search(r'RT\.?\s*(\d+)\s*[\/\\]\s*RW\.?\s*(\d+)', full_text, re.I)
    if m_rtrw:
        addr_parts.append(f"RT {int(m_rtrw.group(1)):02d}/RW {int(m_rtrw.group(2)):02d}")

    m_jl = re.search(r'(?:jalan|jl\.)\s*[:\-]?\s*([^\n]{5,70})', full_text, re.I)
    if m_jl and len(m_jl.group(1).strip()) > 3:
        addr_parts.append(m_jl.group(1).strip())

    for label, prefix in [
        (r'(?:desa|kelurahan|kel\.)\s+([A-Za-z\s]{3,30}?)(?=[,\.\n]|kecamatan|$)', 'Desa'),
        (r'(?:kecamatan|kec\.)\s+([A-Za-z\s]{3,30}?)(?=[,\.\n]|kabupaten|$)', 'Kec.'),
        (r'(?:kabupaten|kab\.|kota)\s+([A-Za-z\s]{3,30}?)(?=[,\.\n]|provinsi|$)', 'Kab.'),
    ]:
        m = re.search(label, full_text, re.I)
        if m:
            addr_parts.append(f"{prefix} {m.group(1).strip()}")

    if addr_parts:
        result['alamat'] = field(', '.join(addr_parts), CONF_HIGH, 'label', 'alamat KK')

    # ── 3. DETEKSI KOLOM TABEL KK ───────────────────────────────────────────
    #
    # KK adalah dokumen dengan kolom: No | Nama | NIK | Tempat/Tgl Lahir | Hub | JK | ...
    # Kita deteksi kolom berdasarkan header row (baris yang mengandung label kolom)
    # Lalu untuk setiap baris data, ambil teks dari kolom yang relevan.

    # Cari header row (mengandung kata 'nik' atau 'nama')
    header_line_idx = -1
    for i, ln in enumerate(lines):
        lt = ln.text.lower()
        if 'nik' in lt and ('nama' in lt or 'kelamin' in lt or 'lahir' in lt):
            header_line_idx = i
            break

    # Deteksi x-koordinat kolom kunci dari header line
    col_nik_x   = None
    col_nama_x  = None
    col_hub_x   = None
    col_jk_x    = None

    if header_line_idx >= 0:
        hl = lines[header_line_idx]
        for w in hl.words:
            wl = normalize_text(w.text).lower()
            if wl == 'nik' and col_nik_x is None:
                col_nik_x = w.cx
            elif wl in ('nama', 'name') and col_nama_x is None:
                col_nama_x = w.cx
            elif re.match(r'hubungan|hub\.?', wl) and col_hub_x is None:
                col_hub_x = w.cx
            elif re.match(r'jenis|kelamin', wl) and col_jk_x is None:
                col_jk_x = w.cx

    # ── 4. DETEKSI ANGGOTA KELUARGA BERDASARKAN NIK ─────────────────────────
    # Strategy: scan semua words, temukan NIK 16-digit.
    # Untuk setiap NIK, ambil baris (line) yang sama via Y proximity.
    # JANGAN menggunakan NIK Nomor KK sebagai anggota.

    nik_words: list[Word] = []
    seen_niks: set[str] = set()
    if no_kk_val:
        seen_niks.add(no_kk_val)

    for w in words:
        n = normalize_nik(normalize_text(w.text))
        if re.match(r'^[1-9]\d{15}$', n) and n not in seen_niks:
            seen_niks.add(n)
            nik_words.append(Word.__new__(Word))
            nw = nik_words[-1]
            nw.text = n; nw.conf = w.conf
            nw.x0 = w.x0; nw.y0 = w.y0
            nw.x1 = w.x1; nw.y1 = w.y1
            nw.cx = w.cx; nw.cy = w.cy; nw.h = w.h

    members = []
    for nw in nik_words:
        # Find all words on the same row (Y ± 2.5 * avg_height)
        row_tol = max(nw.h * 2.5, 20)
        row_words = [w for w in words if abs(w.cy - nw.cy) <= row_tol]
        row_words_sorted = sorted(row_words, key=lambda w: w.x0)

        # Try to get name from same row (excluding the NIK itself)
        name_candidates = []
        role_text = ''

        for rw in row_words_sorted:
            rn = normalize_nik(normalize_text(rw.text))
            if rn == nw.text:
                continue  # skip the NIK word itself

            txt = normalize_text(rw.text)

            # Check if this word looks like a name (alphabetic, not admin)
            cl = clean_name(txt)
            if len(cl) >= 2 and not _NON_NAME.match(cl) and re.search(r'[a-zA-Z]', cl):
                name_candidates.append((cl, rw.conf, rw.x0))

            # Check for role keywords
            if re.search(r'kepala\s*keluarga', txt, re.I): role_text = 'Kepala Keluarga'
            elif re.search(r'\bsuami\b', txt, re.I):       role_text = 'Suami'
            elif re.search(r'\bisteri?i\b', txt, re.I):   role_text = 'Istri'
            elif re.search(r'\banak\b', txt, re.I):        role_text = 'Anak'

        # If col_nama_x known, prefer words near that column x
        if col_nama_x and name_candidates:
            name_candidates.sort(key=lambda x: abs(x[2] - col_nama_x))

        name = ''
        name_conf = 0.0
        if name_candidates:
            # Merge consecutive name tokens sorted by x0
            merged = []
            sorted_nc = sorted(name_candidates, key=lambda x: x[2])
            for nc in sorted_nc:
                if not _NON_NAME.match(nc[0]) and is_plausible_name(nc[0]) or len(nc[0].split()) >= 2:
                    merged.append(nc[0])
                    name_conf = max(name_conf, nc[1])
            if merged:
                name = title_name(' '.join(merged))

        # Also check adjacent lines (row above/below nik row) for name
        if not name:
            for ln in lines:
                if abs(ln.cy - nw.cy) <= row_tol:
                    # entire line text minus the nik
                    lt = normalize_text(ln.text)
                    lt_clean = lt.replace(nw.text, '').strip()
                    lt_clean = re.sub(r'[\|\:\-\d\.]', ' ', lt_clean).strip()
                    c = clean_name(lt_clean)
                    if is_plausible_name(c):
                        name = title_name(c)
                        name_conf = ln.avg_conf
                        break

        # Role: also check context above/below
        if not role_text:
            nearby_lines = [ln for ln in lines if abs(ln.cy - nw.cy) <= row_tol * 2]
            ctx = ' '.join(normalize_text(ln.text) for ln in nearby_lines).lower()
            if re.search(r'kepala\s*keluarga', ctx): role_text = 'Kepala Keluarga'
            elif re.search(r'\bsuami\b', ctx):        role_text = 'Suami'
            elif re.search(r'\bisteri?i\b', ctx):     role_text = 'Istri'
            elif re.search(r'\banak\b', ctx):          role_text = 'Anak'
            else:                                      role_text = 'Anggota Keluarga'

        members.append({
            'nik':      nw.text,
            'name':     name,
            'role':     role_text,
            'nik_conf': round(nw.conf, 3),
            'name_conf': round(name_conf, 3),
            'is_match': False,  # will be set by JS using targetStudentName
        })

    result['members'] = members
    result['has_nik'] = len(members) > 0

    # ── 5. PILIH NIK — hanya jika ada SATU anggota ─────────────────────────
    # Keputusan final NIK diserahkan ke JS (yang punya targetStudentName).
    # Di sini kita hanya auto-select jika ada tepat 1 anggota yang bukan KK.
    non_kk = [mb for mb in members if mb['nik'] != no_kk_val]
    if len(non_kk) == 1:
        mb = non_kk[0]
        conf = CONF_HIGH if mb['nik_conf'] >= 0.8 else CONF_MEDIUM
        result['matched_nik'] = field(mb['nik'], conf, 'layout', '1 anggota terdeteksi')
    # jika >1 anggota, JS yang akan memilih via targetStudentName matching

    # ── 6. KEPALA KELUARGA & ISTRI ──────────────────────────────────────────
    kk_head = next((mb for mb in members if mb['role'] == 'Kepala Keluarga'), None)
    kk_wife = next((mb for mb in members if mb['role'] == 'Istri'), None)
    if kk_head and kk_head['name']:
        result['nama_ayah'] = field(kk_head['name'], CONF_HIGH, 'layout', 'kepala keluarga')
    if kk_wife and kk_wife['name']:
        result['nama_ibu'] = field(kk_wife['name'], CONF_HIGH, 'layout', 'istri')

    return result


# ══════════════════════════════════════════════════════════════════════════════
# API ENDPOINTS
# ══════════════════════════════════════════════════════════════════════════════

@app.get("/health")
def health():
    return {"status": "ok", "engine": "EasyOCR 1.7.2", "version": "2.0.0"}


@app.post("/ocr")
async def ocr_endpoint(file: UploadFile = File(...)):
    """
    Main OCR endpoint.
    Receives: multipart/form-data with 'file' field (image bytes)
    Returns: {success, data: {text, words}}  (compatible with existing Laravel proxy)
    """
    try:
        contents = await file.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return JSONResponse(status_code=400,
                                content={"success": False, "error": "Gambar tidak valid atau rusak"})

        # Run OCR with dual-preprocess strategy
        raw_words = ocr_with_fallback(img)
        norm_words = [normalize_word(w) for w in raw_words]

        lines = group_into_lines(norm_words)
        # Sort lines top→bottom
        lines.sort(key=lambda l: l.cy)

        raw_text = '\n'.join(l.text for l in lines)

        # Serialize words for JS frontend
        words_json = [{
            "text": w.text,
            "confidence": round(w.conf * 100, 1),
            "bbox": {"x0": w.x0, "y0": w.y0, "x1": w.x1, "y1": w.y1}
        } for w in norm_words]

        return {
            "success": True,
            "data": {
                "text": raw_text,
                "words": words_json,
            }
        }

    except Exception as e:
        tb = traceback.format_exc()
        print(f"[OCR ERROR] {tb}")
        return JSONResponse(status_code=500,
                            content={"success": False, "error": str(e)})


@app.post("/ocr/akta")
async def ocr_akta(file: UploadFile = File(...)):
    """
    Dedicated Akta Kelahiran endpoint.
    Returns structured fields directly (no JS parsing needed).
    """
    try:
        contents = await file.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return JSONResponse(status_code=400,
                                content={"success": False, "error": "Gambar tidak valid"})

        raw_words = ocr_with_fallback(img)
        norm_words = [normalize_word(w) for w in raw_words]
        lines = sorted(group_into_lines(norm_words), key=lambda l: l.cy)

        raw_text = '\n'.join(l.text for l in lines)
        fields   = parse_akta(norm_words, lines)

        return {
            "success": True,
            "document_type": "akta",
            "raw_text": raw_text,
            "fields": {k: v['value'] for k, v in fields.items()},
            "confidence": {k: v['confidence'] for k, v in fields.items()},
            "meta": {k: {"source": v["source"], "note": v["note"]} for k, v in fields.items()},
        }

    except Exception as e:
        tb = traceback.format_exc()
        print(f"[AKTA ERROR] {tb}")
        return JSONResponse(status_code=500,
                            content={"success": False, "error": str(e)})


@app.post("/ocr/kk")
async def ocr_kk(file: UploadFile = File(...)):
    """
    Dedicated Kartu Keluarga endpoint.
    Returns structured members list + fields.
    """
    try:
        contents = await file.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return JSONResponse(status_code=400,
                                content={"success": False, "error": "Gambar tidak valid"})

        raw_words = ocr_with_fallback(img)
        norm_words = [normalize_word(w) for w in raw_words]
        lines = sorted(group_into_lines(norm_words), key=lambda l: l.cy)

        raw_text = '\n'.join(l.text for l in lines)
        result   = parse_kk(norm_words, lines)

        return {
            "success": True,
            "document_type": "kk",
            "raw_text": raw_text,
            "fields": {
                "no_kk":  result["no_kk"]["value"],
                "alamat": result["alamat"]["value"],
                "nama_ayah": result["nama_ayah"]["value"],
                "nama_ibu":  result["nama_ibu"]["value"],
            },
            "confidence": {
                "no_kk":  result["no_kk"]["confidence"],
                "alamat": result["alamat"]["confidence"],
                "nama_ayah": result["nama_ayah"]["confidence"],
                "nama_ibu":  result["nama_ibu"]["confidence"],
            },
            "members":   result["members"],
            "has_nik":   result["has_nik"],
            "matched_nik": result["matched_nik"],
        }

    except Exception as e:
        tb = traceback.format_exc()
        print(f"[KK ERROR] {tb}")
        return JSONResponse(status_code=500,
                            content={"success": False, "error": str(e)})


# ══════════════════════════════════════════════════════════════════════════════
if __name__ == "__main__":
    import uvicorn
    print("=" * 60)
    print("  Self-Hosted OCR Service v2.0 (EasyOCR Engine)")
    print("  Endpoint : http://127.0.0.1:8001")
    print("  Health   : GET  /health")
    print("  OCR Raw  : POST /ocr          (multiguna)")
    print("  Akta     : POST /ocr/akta     (structured)")
    print("  KK       : POST /ocr/kk       (structured)")
    print("=" * 60)
    uvicorn.run(app, host="127.0.0.1", port=8001, reload=False)
