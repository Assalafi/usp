<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class ProfilePhotoProcessor
{
    public const MAX_BYTES = 204800;

    public function process(UploadedFile $file, string $directory = 'picture/previews'): string
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);
        $relativePath = trim($directory, '/') . '/' . Str::uuid() . '.jpg';
        $outputPath = $disk->path($relativePath);
        $scriptPath = base_path('scripts/process_profile_photo.py');
        $pythonBinary = is_file('/opt/pg-photo-venv/bin/python3') ? '/opt/pg-photo-venv/bin/python3' : 'python3';

        $modelRoot = storage_path('app/ai-models');
        $process = new Process([$pythonBinary, $scriptPath, '--input', $file->getRealPath(), '--output', $outputPath, '--max-bytes', (string) self::MAX_BYTES, '--model-root', $modelRoot]);
        $process->setEnv(['U2NET_HOME' => $modelRoot]);
        $process->setTimeout(120);
        try {
            $process->run();
        } catch (\Throwable $exception) {
            $disk->delete($relativePath);
            throw ValidationException::withMessages(['picture' => 'Photo preparation took too long. Please choose a smaller, clear JPG or PNG image and try again.']);
        }

        if (!$process->isSuccessful() || !is_file($outputPath)) {
            $disk->delete($relativePath);
            $message = trim($process->getErrorOutput()) ?: 'The photo could not be processed. Upload a clear, front-facing photo with one person.';
            throw ValidationException::withMessages(['picture' => $message]);
        }
        if (filesize($outputPath) > self::MAX_BYTES) {
            $disk->delete($relativePath);
            throw ValidationException::withMessages(['picture' => 'The processed photo is still larger than 200 KB. Please use a clearer photo.']);
        }
        return $relativePath;
    }

    public function processStored(?string $relativePath, string $directory = 'picture/previews'): string
    {
        $disk = Storage::disk('public');
        if (!$relativePath || !$disk->exists('picture/' . ltrim($relativePath, '/')) && !$disk->exists($relativePath)) {
            throw ValidationException::withMessages(['picture' => 'The current profile photo could not be found. Upload a new photo instead.']);
        }
        $storedPath = $disk->exists($relativePath) ? $relativePath : 'picture/' . ltrim($relativePath, '/');
        $file = new UploadedFile($disk->path($storedPath), basename($storedPath), $disk->mimeType($storedPath) ?: 'image/jpeg', null, true);
        return $this->process($file, $directory);
    }
}
