/**
 * ocr-engine.js  —  PPDB Step 2 Frontend OCR Engine  v3.0
 *
 * Arsitektur:
 *   Upload gambar → preprocessImage() → kirim ke Laravel OCR proxy
 *   → Python OCR Service (EasyOCR, self-hosted, port 8001)
 *   → Server melakukan parsing structured (Akta / KK)
 *   → JS menerima {fields, confidence, members}
 *   → applyAktaData() / applyKKData() mengisi form
 *
 * Prinsip:
 *   - ACCURACY OVER AUTOFILL
 *   - HIGH confidence   → autofill
 *   - MEDIUM confidence → autofill + tanda perlu konfirmasi
 *   - LOW / NOT_FOUND   → biarkan kosong, user isi manual
 *   - targetStudentName HANYA untuk matching/validasi, BUKAN sebagai fallback nama
 *   - Dilarang menebak data apapun
 */

'use strict';

// ─── Debug ────────────────────────────────────────────────────────────────────
var OCR_DEBUG = true;
function dbg() {
    if (OCR_DEBUG) console.log.apply(console, ['[OCR]'].concat(Array.from(arguments)));
}
function dbgGroup(label, data) {
    if (!OCR_DEBUG) return;
    try { console.groupCollapsed('[OCR] ' + label); console.log(data); console.groupEnd(); }
    catch (e) {}
}

// ─── Confidence Constants ─────────────────────────────────────────────────────
var CONF = { HIGH: 'HIGH', MEDIUM: 'MEDIUM', LOW: 'LOW', NOT_FOUND: 'NOT_FOUND' };

function makeField(v, c, s, n) {
    return { value: String(v || '').trim(), confidence: c || CONF.NOT_FOUND, source: s || 'none', note: n || '' };
}
function shouldAutofill(f) {
    return f && f.value && (f.confidence === CONF.HIGH || f.confidence === CONF.MEDIUM);
}
function shouldAutofillStrict(f) {
    return f && f.value && f.confidence === CONF.HIGH;
}

// ─── Name Similarity (Jaccard on word tokens) ────────────────────────────────
function nameSimilarity(a, b) {
    if (!a || !b) return 0;
    var wa = new Set(a.toLowerCase().split(/\s+/).filter(function(w) { return w.length > 1; }));
    var wb = new Set(b.toLowerCase().split(/\s+/).filter(function(w) { return w.length > 1; }));
    var inter = 0;
    wa.forEach(function(w) { if (wb.has(w)) inter++; });
    var un = wa.size + wb.size - inter;
    return un === 0 ? 0 : inter / un;
}

// ─── Image Preprocessing ─────────────────────────────────────────────────────
// Kirim gambar resolusi cukup tinggi ke server; preprocessing agresif dilakukan
// di Python server. Di JS kita hanya pastikan ukuran dan quality optimal.
function preprocessImage(img) {
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');
    var ow = img.naturalWidth || img.width;
    var oh = img.naturalHeight || img.height;

    // Upscale gambar kecil ke min 1800px sisi terpanjang
    var longest = Math.max(ow, oh);
    var minPx = 1800, maxPx = 2400;
    var sc = longest < minPx ? (minPx / longest) : (longest > maxPx ? (maxPx / longest) : 1.0);

    canvas.width  = Math.round(ow * sc);
    canvas.height = Math.round(oh * sc);
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

    // Kualitas JPEG 0.93 untuk menjaga detail teks kecil
    return canvas.toDataURL('image/jpeg', 0.93);
}

// ─── Service Health Check ─────────────────────────────────────────────────────
var _serviceOnline = null;
async function checkServiceHealth() {
    if (_serviceOnline !== null) return _serviceOnline;
    try {
        const res = await fetch('/daftar/ocr/health', { method: 'GET' });
        _serviceOnline = res.ok;
    } catch (e) {
        _serviceOnline = false;
    }
    return _serviceOnline;
}

// ─── Upload Progress Helper ───────────────────────────────────────────────────
function setProgress(docType, message) {
    var pctEl = document.getElementById(
        docType === 'akta' ? 'akta-ocr-pct' : 'kk-ocr-pct'
    );
    if (pctEl) pctEl.textContent = message;
}

// ─── MAIN OCR FUNCTION ────────────────────────────────────────────────────────
/**
 * runOCR(imageData, docType)
 * imageData : base64 data URI dari preprocessImage()
 * docType   : 'akta' | 'kk'
 *
 * Mengirim ke endpoint structured (/ocr/akta atau /ocr/kk).
 * Jika server mengembalikan error, coba fallback ke /ocr (raw) dan parse di JS.
 */
async function runOCR(imageData, docType) {
    var progId = docType === 'akta' ? 'akta-ocr-progress' : 'kk-ocr-progress';
    var prog   = document.getElementById(progId);
    var csrf   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    prog.classList.add('visible');
    setProgress(docType, 'Memeriksa layanan OCR…');

    try {
        // 1. Health check — tampilkan peringatan jika offline
        var online = await checkServiceHealth();
        if (!online) {
            throw new Error(
                'OCR Service tidak berjalan. ' +
                'Silakan jalankan: python paddle_ocr_server.py\n' +
                'kemudian muat ulang halaman.'
            );
        }

        setProgress(docType, 'Mengirim gambar ke server OCR…');

        var endpoint = docType === 'akta' ? '/daftar/ocr/akta' : '/daftar/ocr/kk';

        var response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ image: imageData }),
        });

        setProgress(docType, 'Menganalisis dokumen…');

        var resData = await response.json();
        prog.classList.remove('visible');

        if (!resData.success) {
            throw new Error(resData.error || 'Terjadi kesalahan saat OCR');
        }

        dbg('=== OCR SELESAI [' + docType + '] ===');
        dbgGroup('Response', resData);

        if (docType === 'akta') {
            // Tampilkan raw text di debug box
            if (document.getElementById('raw-akta-box')) {
                document.getElementById('raw-akta-box').textContent = resData.raw_text || '';
            }
            applyAktaData(resData);
        } else {
            if (document.getElementById('raw-kk-box')) {
                document.getElementById('raw-kk-box').textContent = resData.raw_text || '';
            }
            applyKKData(resData);
        }

    } catch (err) {
        prog.classList.remove('visible');
        console.error('[OCR] Error:', err);
        alert(
            '⚠️ Gagal membaca dokumen.\n\n' +
            err.message + '\n\n' +
            'Pastikan Python OCR server berjalan di port 8001:\n' +
            '  python paddle_ocr_server.py'
        );
        // Kosongkan form — jangan isi dengan tebakan
        if (docType === 'akta') {
            applyAktaData(null);
        } else {
            applyKKData(null);
        }
    }
}

// ─── Upload Setup ─────────────────────────────────────────────────────────────
function setupUpload(inputId, zoneId, previewId, filenameId, cb) {
    var input   = document.getElementById(inputId);
    var zone    = document.getElementById(zoneId);
    var preview = document.getElementById(previewId);
    var fn      = document.getElementById(filenameId);

    function handleFile(file) {
        if (!file) return;
        fn.textContent = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
        fn.style.color = 'var(--color-primary)';

        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    preview.style.display = 'block';
                    preview.innerHTML =
                        '<img src="' + e.target.result +
                        '" alt="Preview" style="width:100%;max-height:220px;object-fit:contain;background:#f8fafc;padding:0.5rem;">';
                    cb(preprocessImage(img));
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'block';
            preview.innerHTML = '<div style="padding:1rem;font-size:0.875rem;">File tidak didukung. Harap upload gambar.</div>';
        }
    }

    input.addEventListener('change', function() { handleFile(input.files[0]); });
    zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function()  { zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function(e) {
        e.preventDefault(); zone.classList.remove('dragover');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            input.files = e.dataTransfer.files;
            handleFile(e.dataTransfer.files[0]);
        }
    });
    zone.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') input.click();
    });
}

// ══════════════════════════════════════════════════════════════════════════════
//  applyAktaData(resData)
//
//  resData = {
//    fields:     { nama, tempat_lahir, tanggal_lahir, jenis_kelamin, nama_ayah, nama_ibu }
//    confidence: { sama key }  nilai: 'HIGH'|'MEDIUM'|'LOW'|'NOT_FOUND'
//    raw_text:   string
//  }
//
//  Aturan:
//   - HIGH   → autofill, badge hijau "✓ Terbaca"
//   - MEDIUM → autofill, badge kuning "✓ Perlu cek"
//   - lainnya → kosong, badge "⚠️ Manual"
//   - targetStudentName HANYA untuk menampilkan info cocok/tidak, BUKAN override nama
// ══════════════════════════════════════════════════════════════════════════════
function applyAktaData(resData) {
    document.getElementById('akta-result-box').style.display = 'block';

    var dN = document.getElementById('display_nama_akta');
    var dT = document.getElementById('display_tempat_lahir_akta');
    var dD = document.getElementById('display_tanggal_lahir_akta');
    var dJ = document.getElementById('display_jenis_kelamin');
    var bN = document.getElementById('badge-nama-akta');
    var bT = document.getElementById('badge-tempat-akta');
    var bD = document.getElementById('badge-tgl-akta');
    var bJ = document.getElementById('badge-jk-akta');

    // Reset semua ke kosong dulu
    [dN, dT, dD].forEach(function(el) { el.value = ''; });
    dJ.value = '';
    [bN, bT, bD, bJ].forEach(function(b) {
        b.className = 'badge badge-gold';
        b.textContent = '⚠️ Manual';
    });

    if (!resData || !resData.fields) {
        syncAktaInputs();
        return;
    }

    var fields = resData.fields;
    var confs  = resData.confidence || {};
    var meta   = resData.meta || {};
    var target = window.OCR_TARGET_NAME || '';

    function applyField(el, badge, key, labelHigh, labelMed) {
        var val  = fields[key] || '';
        var conf = confs[key]  || CONF.NOT_FOUND;
        if (!val || conf === CONF.NOT_FOUND || conf === CONF.LOW) {
            el.value = '';
            badge.className   = 'badge badge-gold';
            badge.textContent = '⚠️ Manual';
            return;
        }
        el.value = val;
        if (conf === CONF.HIGH) {
            badge.className   = 'badge badge-primary';
            badge.textContent = labelHigh;
        } else {
            badge.className   = 'badge badge-gold';
            badge.textContent = labelMed;
        }
    }

    // Nama: apply dulu, lalu cek kecocokan dengan targetStudentName (hanya info)
    applyField(dN, bN, 'nama', '✓ Terbaca', '✓ Perlu cek');
    if (dN.value && target) {
        var sim = nameSimilarity(dN.value, target);
        if (sim >= 0.5 && confs['nama'] === CONF.HIGH) {
            bN.textContent = '✓ Cocok Step 1';
        } else if (sim < 0.3 && dN.value) {
            // Nama terbaca tapi tidak cocok dengan target → warning, tapi tetap isi
            bN.className   = 'badge badge-gold';
            bN.textContent = '⚠️ Beda Step 1?';
        }
    }

    applyField(dT, bT, 'tempat_lahir',  '✓ Terbaca', '✓ Perlu cek');
    applyField(dD, bD, 'tanggal_lahir', '✓ Terbaca', '✓ Perlu cek');

    // Jenis kelamin
    var jkVal  = fields['jenis_kelamin']  || '';
    var jkConf = confs['jenis_kelamin']   || CONF.NOT_FOUND;
    if (jkVal && jkConf !== CONF.NOT_FOUND && jkConf !== CONF.LOW) {
        dJ.value = jkVal;
        bJ.className   = 'badge badge-primary';
        bJ.textContent = '✓ Terbaca';
    } else {
        bJ.className   = 'badge badge-gold';
        bJ.textContent = '⚠️ Pilih manual';
    }

    dbg('Akta applied:', { nama: dN.value, tempat: dT.value, tgl: dD.value, jk: dJ.value });

    syncAktaInputs();
    [dN, dT, dD, dJ].forEach(function(el) { el.addEventListener('input', syncAktaInputs); });
}

function syncAktaInputs() {
    document.getElementById('input_nama_akta').value          = document.getElementById('display_nama_akta').value;
    document.getElementById('input_tempat_lahir_akta').value  = document.getElementById('display_tempat_lahir_akta').value;
    document.getElementById('input_tanggal_lahir_akta').value = document.getElementById('display_tanggal_lahir_akta').value;
    document.getElementById('input_jenis_kelamin').value      = document.getElementById('display_jenis_kelamin').value;
}


// ══════════════════════════════════════════════════════════════════════════════
//  applyKKData(resData)
//
//  resData = {
//    fields:     { no_kk, alamat, nama_ayah, nama_ibu }
//    confidence: { sama key }
//    members:    [{ nik, name, role, nik_conf, name_conf, is_match }]
//    matched_nik: { value, confidence, ... }
//    has_nik:    bool
//  }
//
//  Aturan NIK:
//   - Jika server sudah auto-select (1 anggota) → tampilkan
//   - Jika >1 anggota → tampilkan daftar, user pilih
//   - targetStudentName digunakan untuk highlighting anggota yang kemungkinan cocok
//   - JANGAN auto-select berdasarkan targetStudentName — biarkan user konfirmasi
// ══════════════════════════════════════════════════════════════════════════════
function applyKKData(resData) {
    document.getElementById('kk-result-box').style.display = 'block';

    var dNik  = document.getElementById('display_nik_siswa');
    var bNik  = document.getElementById('badge-nik-kk');
    var mList = document.getElementById('kk-members-list');
    var mCont = document.getElementById('kk-members-container');

    // Reset
    dNik.value = '';
    bNik.className   = 'badge badge-gold';
    bNik.textContent = '⚠️ Manual';
    mList.innerHTML  = '';
    mCont.style.display = 'none';

    if (!resData || !resData.fields) {
        syncKKInputs();
        return;
    }

    var fields  = resData.fields  || {};
    var confs   = resData.confidence || {};
    var members = resData.members || [];
    var matched = resData.matched_nik || {};
    var target  = window.OCR_TARGET_NAME || '';

    // ── NIK Auto-select (hanya jika server yakin: 1 anggota, HIGH/MEDIUM) ──
    var autoNik = '';
    if (matched.value && (matched.confidence === CONF.HIGH || matched.confidence === CONF.MEDIUM)) {
        autoNik = matched.value;
        dNik.value = autoNik;
        bNik.className   = 'badge badge-primary';
        bNik.textContent = matched.confidence === CONF.HIGH ? '✓ NIK Terbaca' : '✓ Perlu konfirmasi';
    }

    // ── Daftar Anggota Keluarga ──────────────────────────────────────────────
    if (members.length > 0) {
        mCont.style.display = 'block';
        mList.innerHTML = '';

        // Hitung similarity dengan targetStudentName untuk highlighting
        // HANYA dipakai untuk visual highlight, TIDAK untuk auto-fill
        var membersSorted = members.map(function(mb) {
            var sim = (target && mb.name) ? nameSimilarity(mb.name, target) : 0;
            return Object.assign({}, mb, { _sim: sim });
        });

        // Sort: name-match dulu, lalu kepala keluarga
        membersSorted.sort(function(a, b) {
            if (b._sim !== a._sim) return b._sim - a._sim;
            var aK = /kepala/i.test(a.role) ? 1 : 0;
            var bK = /kepala/i.test(b.role) ? 1 : 0;
            return bK - aK;
        });

        membersSorted.forEach(function(mb) {
            var isSelected = autoNik && mb.nik === autoNik;
            var isSimilar  = mb._sim >= 0.4 && !isSelected;

            var label = document.createElement('label');
            label.style.cssText =
                'display:flex;justify-content:space-between;align-items:center;' +
                'padding:10px 14px;border:1.5px solid ' +
                (isSelected ? '#16a34a' : isSimilar ? '#f59e0b' : '#e5e7eb') + ';' +
                'margin-bottom:6px;background:' +
                (isSelected ? '#f0fdf4' : isSimilar ? '#fffbeb' : '#fff') + ';' +
                'cursor:pointer;border-radius:6px;';

            var nameDisplay  = mb.name || '<em style="color:#9ca3af">Nama tidak terbaca</em>';
            var confBadge    = '';
            if (mb.name && mb.name_conf >= 0.8) {
                confBadge = ' <span style="font-size:0.65rem;color:#6b7280;">(conf ' + Math.round(mb.name_conf * 100) + '%)</span>';
            }
            var simBadge = isSimilar
                ? ' <span style="font-size:0.65rem;background:#fef3c7;color:#92400e;padding:1px 5px;border-radius:3px;">Kemungkinan cocok</span>'
                : '';

            label.innerHTML =
                '<div style="display:flex;gap:10px;align-items:center;">' +
                  '<input type="radio" name="kk_r" value="' + mb.nik + '" ' + (isSelected ? 'checked' : '') + '>' +
                  '<div>' +
                    '<strong>' + nameDisplay + '</strong>' + confBadge + simBadge + '<br>' +
                    '<small style="color:#6b7280;">' + (mb.role || 'Anggota Keluarga') + '</small>' +
                  '</div>' +
                '</div>' +
                '<span style="font-family:monospace;font-size:0.85rem;font-weight:700;">' + mb.nik + '</span>';

            label.querySelector('input').addEventListener('change', function() {
                dNik.value = mb.nik;
                bNik.className   = 'badge badge-primary';
                bNik.textContent = '✓ Dipilih';
                // Visual update
                document.querySelectorAll('#kk-members-list label').forEach(function(l) {
                    l.style.borderColor = '#e5e7eb';
                    l.style.background  = '#fff';
                });
                label.style.borderColor = '#16a34a';
                label.style.background  = '#f0fdf4';
                syncKKInputs();
            });

            mList.appendChild(label);
        });

        // Pesan panduan
        var guide = document.createElement('p');
        guide.style.cssText = 'font-size:0.8rem;color:#6b7280;margin-top:4px;margin-bottom:0;';
        guide.textContent = members.length > 1
            ? 'Klik untuk memilih NIK calon siswa (bukan NIK ayah/ibu atau Nomor KK).'
            : 'Satu anggota terdeteksi. Konfirmasi NIK di atas sudah benar.';
        mList.appendChild(guide);
    }

    // ── Alamat & Orang Tua ───────────────────────────────────────────────────
    var dA  = document.getElementById('display_alamat_kk');
    var dAy = document.getElementById('display_nama_ayah');
    var dIb = document.getElementById('display_nama_ibu');

    function applySimpleField(el, key) {
        var val  = fields[key]  || '';
        var conf = confs[key]   || CONF.NOT_FOUND;
        el.value = (val && conf !== CONF.NOT_FOUND && conf !== CONF.LOW) ? val : '';
    }

    applySimpleField(dA,  'alamat');
    applySimpleField(dAy, 'nama_ayah');
    applySimpleField(dIb, 'nama_ibu');

    dbg('KK applied:', {
        nik: dNik.value,
        alamat: dA.value,
        ayah: dAy.value,
        ibu: dIb.value,
        members_count: members.length
    });

    syncKKInputs();
    [dNik, dA, dAy, dIb].forEach(function(el) { el.addEventListener('input', syncKKInputs); });
}

function syncKKInputs() {
    var rn = (document.getElementById('display_nik_siswa').value || '').replace(/\D/g, '').slice(0, 16);
    document.getElementById('display_nik_siswa').value   = rn;
    document.getElementById('input_nik').value           = rn;
    document.getElementById('input_alamat_kk').value     = document.getElementById('display_alamat_kk').value;
    document.getElementById('input_nama_ayah_kk').value  = document.getElementById('display_nama_ayah').value;
    document.getElementById('input_nama_ibu_kk').value   = document.getElementById('display_nama_ibu').value;
}
