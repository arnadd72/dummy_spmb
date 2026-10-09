<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * OcrController — Proxy ke Self-Hosted OCR Service (Python/EasyOCR)
 *
 * Arsitektur:
 *   Browser → Laravel (CSRF-protected) → Python OCR Service (port 8001) → response JSON
 *
 * Endpoint Python tersedia:
 *   POST /ocr        → raw OCR (text + words array, diparse JS)
 *   POST /ocr/akta   → structured Akta Kelahiran response
 *   POST /ocr/kk     → structured KK response
 */
class OcrController extends Controller
{
    private const PYTHON_URL = 'http://127.0.0.1:8001';
    private const TIMEOUT    = 90; // seconds — EasyOCR bisa lambat untuk gambar besar

    /**
     * POST /daftar/ocr
     * General OCR endpoint (raw text + words with bounding boxes).
     * Digunakan oleh JS frontend untuk parsing manual (applyAktaData / applyKKData).
     */
    public function process(Request $request)
    {
        $request->validate([
            'image' => 'required|string', // base64 data URI
        ]);

        try {
            $imageContent = $this->decodeBase64Image($request->input('image'), $type);

            $response = Http::timeout(self::TIMEOUT)
                ->attach('file', $imageContent, 'document.' . $type)
                ->post(self::PYTHON_URL . '/ocr');

            return $this->proxyResponse($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /daftar/ocr/akta
     * Structured Akta Kelahiran OCR.
     * Python service melakukan parsing — response langsung berisi fields + confidence.
     */
    public function processAkta(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {
            $imageContent = $this->decodeBase64Image($request->input('image'), $type);

            $response = Http::timeout(self::TIMEOUT)
                ->attach('file', $imageContent, 'document.' . $type)
                ->post(self::PYTHON_URL . '/ocr/akta');

            return $this->proxyResponse($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /daftar/ocr/kk
     * Structured Kartu Keluarga OCR.
     * Python service mendeteksi tabel + anggota keluarga berdasarkan layout.
     */
    public function processKk(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
        ]);

        try {
            $imageContent = $this->decodeBase64Image($request->input('image'), $type);

            $response = Http::timeout(self::TIMEOUT)
                ->attach('file', $imageContent, 'document.' . $type)
                ->post(self::PYTHON_URL . '/ocr/kk');

            return $this->proxyResponse($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /daftar/ocr/health
     * Check apakah Python service sedang berjalan.
     */
    public function health()
    {
        try {
            $response = Http::timeout(5)->get(self::PYTHON_URL . '/health');
            if ($response->successful()) {
                return response()->json(['status' => 'ok', 'service' => $response->json()]);
            }
            return response()->json(['status' => 'error', 'message' => 'Service tidak merespons'], 503);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'offline',
                'message' => 'OCR Service tidak berjalan. Jalankan: python paddle_ocr_server.py',
            ], 503);
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Decode base64 data URI → raw image bytes.
     * @param  string  $imageData  base64 data URI
     * @param  string  &$type      output: 'jpeg' | 'png' | 'webp'
     * @return string  raw bytes
     */
    private function decodeBase64Image(string $imageData, ?string &$type): string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $match)) {
            throw new \InvalidArgumentException('Format gambar base64 tidak valid');
        }

        $type = strtolower($match[1]);
        if ($type === 'jpg') $type = 'jpeg';

        if (!in_array($type, ['jpeg', 'png', 'webp', 'bmp', 'tiff'])) {
            throw new \InvalidArgumentException("Tipe gambar '{$type}' tidak didukung");
        }

        $raw = base64_decode(substr($imageData, strpos($imageData, ',') + 1));
        if ($raw === false || strlen($raw) < 100) {
            throw new \InvalidArgumentException('Data gambar tidak valid atau terlalu kecil');
        }

        return $raw;
    }

    private function proxyResponse($response)
    {
        if ($response->successful()) {
            return response()->json($response->json());
        }

        $body = $response->body();
        $status = $response->status();

        // Coba parse JSON error dari Python service
        $json = json_decode($body, true);
        $errMsg = $json['error'] ?? "Python OCR Service error (HTTP {$status})";

        return response()->json([
            'success' => false,
            'error'   => $errMsg,
        ], $status >= 500 ? 502 : $status);
    }
}
