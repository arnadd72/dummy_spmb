<?php

namespace App\Support;

/**
 * Generator barcode Code 39 tanpa dependensi eksternal.
 *
 * Code 39 dipilih karena setiap karakter hanya butuh 9 elemen (5 garis & 4 spasi),
 * sehingga cukup digambar sebagai SVG inline dan siap dicetak/dipindai.
 */
class Code39Barcode
{
    /**
     * Pola 9 elemen per karakter. Digit '1' = elemen lebar, '0' = elemen sempit.
     * Urutan elemen: garis, spasi, garis, spasi, ... (indeks genap = garis).
     */
    private const PATTERNS = [
        '0' => '000110100', '1' => '100100001', '2' => '001100001', '3' => '101100000',
        '4' => '000110001', '5' => '100110000', '6' => '001110000', '7' => '000100101',
        '8' => '100100100', '9' => '001100100', 'A' => '100001001', 'B' => '001001001',
        'C' => '101001000', 'D' => '000011001', 'E' => '100011000', 'F' => '001011000',
        'G' => '000001101', 'H' => '100001100', 'I' => '001001100', 'J' => '000011100',
        'K' => '100000011', 'L' => '001000011', 'M' => '101000010', 'N' => '000010011',
        'O' => '100010010', 'P' => '001010010', 'Q' => '000000111', 'R' => '100000110',
        'S' => '001000110', 'T' => '000010110', 'U' => '110000001', 'V' => '011000001',
        'W' => '111000000', 'X' => '010010001', 'Y' => '110010000', 'Z' => '011010000',
        '-' => '010000101', '.' => '110000100', ' ' => '011000100', '$' => '010101000',
        '/' => '010100010', '+' => '010001010', '%' => '000101010', '*' => '010010100',
    ];

    /**
     * Render barcode Code 39 sebagai string SVG.
     *
     * @param  string  $data       Data yang akan dikodekan.
     * @param  int     $height     Tinggi barcode dalam piksel.
     * @param  float   $narrow     Lebar satu modul sempit.
     * @param  int     $quietZone  Jumlah modul kosong di kiri & kanan.
     */
    public static function svg(string $data, int $height = 56, float $narrow = 1.5, int $quietZone = 10): string
    {
        $clean = self::sanitize($data);

        // Karakter '*' adalah penanda start/stop pada Code 39.
        $encoded = '*' . $clean . '*';

        $rects = [];
        $x = $quietZone * $narrow;
        $first = true;

        foreach (str_split($encoded) as $char) {
            if (!isset(self::PATTERNS[$char])) {
                continue;
            }

            // Jarak antar karakter (inter-character gap).
            if (!$first) {
                $x += $narrow;
            }
            $first = false;

            $pattern = self::PATTERNS[$char];

            for ($i = 0; $i < 9; $i++) {
                $width = $pattern[$i] === '1' ? $narrow * 3 : $narrow;
                $isBar = $i % 2 === 0;

                if ($isBar) {
                    $rects[] = sprintf(
                        '<rect x="%s" y="0" width="%s" height="%d"/>',
                        self::num($x),
                        self::num($width),
                        $height
                    );
                }

                $x += $width;
            }
        }

        $totalWidth = $x + ($quietZone * $narrow);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%s" height="%d" viewBox="0 0 %s %d" role="img" aria-label="Barcode %s" fill="#000">%s</svg>',
            self::num($totalWidth),
            $height,
            self::num($totalWidth),
            $height,
            e($clean),
            implode('', $rects)
        );
    }

    /**
     * Buang karakter yang tidak didukung Code 39 agar barcode tetap valid.
     */
    private static function sanitize(string $data): string
    {
        $upper = strtoupper($data);
        $out = '';

        foreach (str_split($upper) as $char) {
            if (isset(self::PATTERNS[$char])) {
                $out .= $char;
            }
        }

        // Code 39 membutuhkan minimal satu karakter data.
        return $out !== '' ? $out : 'PPDB';
    }

    /**
     * Format angka desimal tanpa nol di belakang koma, agar SVG lebih rapi.
     */
    private static function num(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }
}
