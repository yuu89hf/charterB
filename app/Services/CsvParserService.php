<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class CsvParserService
{
    /**
     * Parses the uploaded CSV file and extracts valid names from Column A.
     *
     * @param UploadedFile $csvFile
     * @param array $filters Array of filters: row_start, row_end, exclude_rows
     * @return array
     */
    public function extractNames(UploadedFile $csvFile, array $filters = []): array
    {
        $names = [];
        $handle = fopen($csvFile->getRealPath(), 'r');
        
        if ($handle === false) {
            return [];
        }

        // Auto-detect delimiter
        $delimiter = ',';
        $firstLine = fgets($handle);
        if ($firstLine !== false) {
            $commaCount = substr_count($firstLine, ',');
            $semicolonCount = substr_count($firstLine, ';');
            if ($semicolonCount > $commaCount) {
                $delimiter = ';';
            }
        }
        rewind($handle);

        $rowStart    = $filters['row_start'] ?? null;
        $rowEnd      = $filters['row_end'] ?? null;
        $excludeRows = $filters['exclude_rows'] ?? [];

        $firstRow = true;
        $rowNum = 0;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if (!isset($data[0]) || trim($data[0]) === '') {
                continue;
            }

            $cellValue = trim($data[0]);

            // Skip header row
            if ($firstRow) {
                $firstRow = false;
                $lower = strtolower($cellValue);
                if (in_array($lower, ['nama', 'name', 'no', 'no.', 'nomor', 'number'])) {
                    continue;
                }
            }

            // Apply filters
            if ($rowStart !== null && $rowNum < $rowStart) {
                continue;
            }
            if ($rowEnd !== null && $rowNum > $rowEnd) {
                continue;
            }
            if (in_array($rowNum, $excludeRows)) {
                continue;
            }

            $names[] = $cellValue;
        }

        fclose($handle);

        return $names;
    }
}
