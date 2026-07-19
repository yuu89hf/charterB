<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class WorkspaceExportService
{
    protected $csvParser;
    protected $generator;

    public function __construct(CsvParserService $csvParser, CertificateGeneratorService $generator)
    {
        $this->csvParser = $csvParser;
        $this->generator = $generator;
    }

    /**
     * Orchestrates parsing CSV and generating the final ZIP archive.
     * 
     * @return array|null Returns ['zip_name' => $name] on success, or null on failure.
     */
    public function export(UploadedFile $template, UploadedFile $csv, array $config, ?string $progressId = null): ?array
    {
        // 1. Parse Names
        $filters = [
            'row_start' => $config['row_start'] ?? null,
            'row_end'   => $config['row_end'] ?? null,
            'exclude_rows' => $config['row_exclude'] ?? [],
        ];
        
        $names = $this->csvParser->extractNames($csv, $filters);
        if (empty($names)) {
            throw new \Exception('No valid names found in the uploaded CSV.');
        }

        // Initialize progress cache
        if ($progressId) {
            cache()->put("progress_{$progressId}", 0, 120);
        }

        // 2. Prepare Zip Archive Path
        $zipName = 'workspace_export_' . time() . '.zip';
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

        // 3. Generate Certificates
        $success = $this->generator->generateToZip(
            $names,
            $template->getRealPath(),
            $config,
            $zipPath,
            $progressId
        );

        if (!$success) {
            throw new \Exception('Failed to create the ZIP archive.');
        }

        if ($progressId) {
            cache()->put("progress_{$progressId}", 100, 120);
        }

        return ['zip_name' => $zipName];
    }
}
