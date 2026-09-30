<?php

namespace App\Console\Commands;

use App\Services\FaceSearchService;
use Illuminate\Console\Command;

class IndexFaceSearch extends Command
{
    protected $signature = 'face-search:index {scope=ug_all : ug_students, ug_staff, or ug_all}';
    protected $description = 'Build the encrypted face-search index for UG students and staff';

    public function handle(FaceSearchService $service): int
    {
        $scope = $this->argument('scope');
        if (!in_array($scope, ['ug_students', 'ug_staff', 'ug_all'], true)) {
            $this->error('Scope must be ug_students, ug_staff, or ug_all.');
            return self::FAILURE;
        }
        $this->info('Building face-search index. This can take a while for the first run.');
        $counts = $service->indexAll($scope, function ($source, $record, $result) {
            $this->line(sprintf('%s %-12s %s', $source, $record->username ?? $record->id, $result));
        });
        $this->newLine();
        $this->table(['Indexed', 'Skipped', 'Failed'], [[$counts['indexed'], $counts['skipped'], $counts['failed']]]);
        return self::SUCCESS;
    }
}
