<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class FaceSearchService
{
    public const UG_STUDENTS = 'ug_students';
    public const UG_STAFF = 'ug_staff';

    public function embeddingFromUpload(UploadedFile $file): array
    {
        return $this->runEmbedding($file->getRealPath());
    }

    public function search(UploadedFile $file, string $scope = self::UG_STUDENTS, int $limit = 8): array
    {
        $probe = $this->embeddingFromUpload($file);
        $scopes = $scope === 'ug_all' ? [self::UG_STUDENTS, self::UG_STAFF] : [$scope];
        $rows = DB::table('face_search_embeddings')
            ->whereIn('source', $scopes)
            ->where('status', 'ready')
            ->whereNotNull('embedding')
            ->get();

        $matches = [];
        foreach ($rows as $row) {
            try {
                $candidate = json_decode(Crypt::decryptString($row->embedding), true, 4, JSON_THROW_ON_ERROR);
                $score = $this->cosine($probe['embedding'], $candidate);
                $metadata = json_decode((string) $row->metadata, true) ?: [];
                $matches[] = [
                    'source' => $row->source,
                    'record_key' => $row->record_key,
                    'score' => round($score, 5),
                    'confidence' => $this->confidence($score),
                    'quality' => (float) ($row->quality ?? 0),
                    'metadata' => $metadata,
                ];
            } catch (\Throwable $e) {
                // A corrupt index row should not make an otherwise valid search fail.
            }
        }

        usort($matches, fn ($a, $b) => $b['score'] <=> $a['score']);
        $topScore = $matches[0]['score'] ?? null;
        $secondScore = $matches[1]['score'] ?? null;
        $threshold = (float) config('services.face_search.threshold', 0.48);
        $margin = (float) config('services.face_search.min_margin', 0.045);
        foreach ($matches as $index => &$match) {
            $match['rank'] = $index + 1;
            $match['match_state'] = ($index === 0 && $topScore !== null && $topScore >= $threshold && ($secondScore === null || ($topScore - $secondScore) >= $margin))
                ? 'high_confidence'
                : (($match['score'] >= $threshold) ? 'review' : 'low_confidence');
        }
        unset($match);

        return [
            'probe_quality' => (float) ($probe['quality'] ?? 0),
            'faces' => (int) ($probe['faces'] ?? 1),
            'indexed' => $rows->count(),
            'threshold' => $threshold,
            'results' => array_slice($matches, 0, max(1, min(20, $limit))),
        ];
    }

    public function indexAll(?string $scope = null, ?callable $output = null): array
    {
        $sources = $scope && $scope !== 'ug_all' ? [$scope] : [self::UG_STUDENTS, self::UG_STAFF];
        $counts = ['indexed' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($sources as $source) {
            $records = $source === self::UG_STAFF
                ? DB::table('staff')->select('id', 'username', 'name', 'faculty', 'department', 'picture')->whereNotNull('picture')->where('picture', '!=', '')->cursor()
                : DB::table('students')->select('id', 'username', 'fullname', 'faculty', 'department', 'program', 'level', 'picture')->whereNotNull('picture')->where('picture', '!=', '')->cursor();
            foreach ($records as $record) {
                $result = $this->indexRecord($source, $record);
                $counts[$result]++;
                if ($output) { $output($source, $record, $result); }
            }
        }
        return $counts;
    }

    public function indexRecord(string $source, object $record): string
    {
        $key = (string) ($record->id ?? $record->username);
        $relative = (string) ($record->picture ?? '');
        $absolute = $this->photoPath($relative);
        if (!$absolute || !is_file($absolute)) {
            $this->storeError($source, $key, $relative, 'Photo file not found.');
            return 'skipped';
        }
        $hash = hash_file('sha256', $absolute);
        $existing = DB::table('face_search_embeddings')->where(['source' => $source, 'record_key' => $key])->first();
        if ($existing && $existing->photo_hash === $hash && $existing->status === 'ready') {
            return 'skipped';
        }
        try {
            $embedding = $this->runEmbedding($absolute);
            $metadata = $source === self::UG_STAFF
                ? ['name' => $record->name, 'identifier' => $record->username, 'faculty' => $record->faculty, 'department' => $record->department, 'photo_url' => asset('storage/picture/' . $relative)]
                : ['name' => $record->fullname, 'identifier' => $record->username, 'faculty' => $record->faculty, 'department' => $record->department, 'program' => $record->program, 'level' => $record->level, 'photo_url' => asset('storage/picture/' . $relative)];
            DB::table('face_search_embeddings')->updateOrInsert(
                ['source' => $source, 'record_key' => $key],
                ['photo_path' => $relative, 'photo_hash' => $hash, 'embedding' => Crypt::encryptString(json_encode($embedding['embedding'])), 'metadata' => json_encode($metadata), 'quality' => $embedding['quality'] ?? null, 'status' => 'ready', 'error_message' => null, 'indexed_at' => now(), 'updated_at' => now(), 'created_at' => $existing?->created_at ?? now()]
            );
            return 'indexed';
        } catch (\Throwable $e) {
            $this->storeError($source, $key, $relative, $e->getMessage());
            return 'failed';
        }
    }

    private function storeError(string $source, string $key, string $path, string $message): void
    {
        DB::table('face_search_embeddings')->updateOrInsert(
            ['source' => $source, 'record_key' => $key],
            ['photo_path' => $path ?: null, 'status' => 'error', 'error_message' => mb_substr($message, 0, 1000), 'updated_at' => now(), 'created_at' => now()]
        );
    }

    private function photoPath(string $relative): ?string
    {
        $relative = ltrim($relative, '/');
        foreach ([storage_path('app/public/picture/' . $relative), storage_path('app/public/' . $relative), public_path('storage/picture/' . $relative)] as $path) {
            if (is_file($path)) return $path;
        }
        return null;
    }

    private function runEmbedding(string $input): array
    {
        $python = (string) config('services.face_search.python', 'python3');
        $script = base_path('scripts/face_search_embedding.py');
        $modelDir = (string) config('services.face_search.model_dir', storage_path('app/ai-models/face-search'));
        $process = new Process([$python, $script, '--input', $input, '--model-dir', $modelDir]);
        $process->setTimeout((int) config('services.face_search.timeout', 45));
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'The image could not be analysed.');
        }
        $data = json_decode(trim($process->getOutput()), true);
        if (!is_array($data) || empty($data['embedding'])) throw new \RuntimeException('The image could not be analysed.');
        return $data;
    }

    private function cosine(array $left, array $right): float
    {
        $dot = $leftNorm = $rightNorm = 0.0;
        $count = min(count($left), count($right));
        for ($i = 0; $i < $count; $i++) { $dot += (float) $left[$i] * (float) $right[$i]; $leftNorm += (float) $left[$i] ** 2; $rightNorm += (float) $right[$i] ** 2; }
        return ($leftNorm > 0 && $rightNorm > 0) ? $dot / (sqrt($leftNorm) * sqrt($rightNorm)) : -1.0;
    }

    private function confidence(float $score): string
    {
        if ($score >= 0.62) return 'Very high';
        if ($score >= 0.55) return 'High';
        if ($score >= 0.48) return 'Review';
        return 'Low';
    }
}
