<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = [
        'pertanyaan', 'jawaban', 'keywords', 'kategori', 'is_active',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Cari FAQ berdasarkan keyword similarity (Jaccard-like)
     */
    public static function cari(string $query, int $maxResults = 3): array
    {
        // Stopwords bahasa Indonesia dasar
        $stopwords = ['apa', 'bagaimana', 'cara', 'di', 'ke', 'dari', 'yang', 'untuk', 'dengan', 'dan', 'atau', 'ini', 'itu', 'saya', 'ada', 'bisa', 'apakah', 'berapa', 'kapan', 'dimana'];

        $queryWords = array_filter(
            explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/i', '', $query))),
            fn($w) => strlen($w) > 2 && !in_array($w, $stopwords)
        );

        if (empty($queryWords)) {
            return static::where('is_active', true)->take($maxResults)->get()->toArray();
        }

        $faqs = static::where('is_active', true)->get();
        $results = [];

        foreach ($faqs as $faq) {
            $keywords = array_map('strtolower', $faq->keywords ?? []);
            $pertanyaanWords = explode(' ', strtolower($faq->pertanyaan));
            $allWords = array_unique(array_merge($keywords, $pertanyaanWords));

            // Jaccard similarity
            $intersection = count(array_intersect($queryWords, $allWords));
            $union = count(array_unique(array_merge($queryWords, $allWords)));
            $score = $union > 0 ? $intersection / $union : 0;

            if ($score > 0) {
                $results[] = ['faq' => $faq, 'score' => $score];
            }
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice(array_map(fn($r) => [
            'faq'   => $r['faq'],
            'score' => $r['score'],
            'cukup' => $r['score'] >= 0.1,
        ], $results), 0, $maxResults);
    }
}
