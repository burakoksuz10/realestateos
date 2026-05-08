<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;

class DeduplicateNewsCommand extends Command
{
    protected $signature = 'news:dedup {--threshold=60 : Similarity percentage threshold}';
    protected $description = 'Remove duplicate news articles based on title similarity';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $articles  = NewsArticle::orderBy('id')->get();

        $keptTitles = [];
        $deleted    = 0;

        foreach ($articles as $article) {
            $isDuplicate = false;
            foreach ($keptTitles as $kept) {
                $score = $this->wordJaccard($article->title, $kept);
                if ($score * 100 >= $threshold) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) {
                $article->delete();
                $deleted++;
            } else {
                $keptTitles[] = $article->title;
            }
        }

        $remaining = NewsArticle::count();
        $this->info("Silindi: {$deleted} | Kalan: {$remaining}");

        return Command::SUCCESS;
    }

    private function wordJaccard(string $a, string $b): float
    {
        $wa = array_unique($this->words($a));
        $wb = array_unique($this->words($b));
        if (!$wa || !$wb) return 0.0;

        if (count($wa) > count($wb)) {
            [$wa, $wb] = [$wb, $wa];
        }

        $covered = count(array_intersect($wa, $wb));
        return $covered / count($wa);
    }

    private function words(string $title): array
    {
        $title = preg_replace('/\s[-–]\s[^-–]+$/', '', $title);
        $title = mb_strtolower($title, 'UTF-8');
        $title = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
        $words = preg_split('/\s+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter($words, fn($w) => mb_strlen($w, 'UTF-8') > 2));
    }
}
