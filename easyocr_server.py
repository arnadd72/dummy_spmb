from fastapi import FastAPI, File, UploadFile
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import easyocr
import numpy as np
import cv2
import io

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# Inisialisasi EasyOCR (gpu=False agar jalan di semua PC tanpa GPU)
print("Memuat model EasyOCR (mungkin butuh waktu beberapa detik)...")
reader = easyocr.Reader(['id', 'en'], gpu=False)
print("Model EasyOCR siap!")

def preprocess_image(img):
    """
    Preprocessing gambar untuk OCR yang lebih akurat:
    - Grayscale
    - Denoise
    - Adaptive threshold untuk kontras teks
    - Upscaling gambar kecil
    """
    # Upscale jika gambar terlalu kecil (akta/kk biasanya scan kecil)
    h, w = img.shape[:2]
    if max(h, w) < 1600:
        scale = 1600 / max(h, w)
        img = cv2.resize(img, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)

    # Convert ke grayscale
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    # Denoise
    denoised = cv2.fastNlMeansDenoising(gray, h=10, templateWindowSize=7, searchWindowSize=21)

    # Adaptive threshold (bagus untuk dokumen dengan pencahayaan tidak merata)
    thresh = cv2.adaptiveThreshold(
        denoised, 255,
        cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY,
        31, 10
    )

    # Kembalikan ke BGR agar EasyOCR happy
    result = cv2.cvtColor(thresh, cv2.COLOR_GRAY2BGR)
    return result

@app.post("/ocr")
async def process_ocr(file: UploadFile = File(...)):
    try:
        # Baca gambar dari request
        contents = await file.read()
        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return JSONResponse(status_code=400, content={"success": False, "error": "Gambar tidak valid"})

        # Preprocessing
        img_processed = preprocess_image(img)

        # Jalankan EasyOCR dengan paragraph=True untuk grouping baris lebih baik
        result = reader.readtext(
            img_processed,
            detail=1,
            paragraph=False,  # False = per-word bbox, lebih akurat untuk parsing
            width_ths=0.7,
            height_ths=0.7,
            decoder='beamsearch',   # beamsearch lebih akurat daripada greedy
            beamWidth=10,
            batch_size=1,
            workers=0,
        )

        words = []
        raw_text_lines = []

        for (bbox, text, prob) in result:
            # bbox berisi 4 titik koordinat: [[x0,y0],[x1,y0],[x1,y1],[x0,y1]]
            x0 = float(min(p[0] for p in bbox))
            y0 = float(min(p[1] for p in bbox))
            x1 = float(max(p[0] for p in bbox))
            y1 = float(max(p[1] for p in bbox))

            text_clean = text.strip()
            if not text_clean:
                continue

            raw_text_lines.append(text_clean)
            words.append({
                "text": text_clean,
                "confidence": round(float(prob) * 100, 1),
                "bbox": {
                    "x0": x0,
                    "y0": y0,
                    "x1": x1,
                    "y1": y1
                }
            })

        full_text = "\n".join(raw_text_lines)

        return {
            "success": True,
            "data": {
                "text": full_text,
                "words": words
            }
        }

    except Exception as e:
        import traceback
        tb = traceback.format_exc()
        print("ERROR:", tb)
        return JSONResponse(status_code=500, content={"success": False, "error": str(e)})

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="127.0.0.1", port=8001, reload=False)
