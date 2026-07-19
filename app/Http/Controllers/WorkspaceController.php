<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateWorkspaceRequest;
use App\Services\WorkspaceExportService;
use Illuminate\Support\Facades\Cookie;
use Exception;

class WorkspaceController extends Controller
{
    protected $exportService;

    public function __construct(WorkspaceExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function index()
    {
        return view('workspace.index');
    }

    public function generate(GenerateWorkspaceRequest $request)
    {
        // Prevent timeout for large CSVs
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $validated = $request->validated();

        $excludeInput = $request->input('row_exclude', '');
        $excludeRows = [];
        if (!empty(trim($excludeInput))) {
            $excludeRows = preg_split('/[^0-9]+/', trim($excludeInput));
            $excludeRows = array_map('intval', array_filter($excludeRows, 'strlen'));
        }

        $config = [
            'percent_x'         => (float) $validated['x_pos'],
            'percent_y'         => (float) $validated['y_pos'],
            'format'            => $validated['format'] ?? 'png',
            'font_scale'        => (float) ($validated['font_scale'] ?? 100),
            'resolution_scale'  => (float) ($validated['resolution_scale'] ?? 100),
            'font_family'       => $validated['font_family'] ?? 'Roboto-Bold',
            'row_start'         => $validated['row_start'] ? (int) $validated['row_start'] : null,
            'row_end'           => $validated['row_end'] ? (int) $validated['row_end'] : null,
            'row_exclude'       => $excludeRows,
            'use_paper'         => ($validated['use_paper'] ?? 'n') === 'y',
            'paper_size'        => $validated['paper_size'] ?? 'A4',
            'paper_orientation' => $validated['paper_orientation'] ?? 'auto',
            'fit_mode'          => $validated['fit_mode'] ?? 'smaller',
            'img_x'             => (float) ($validated['img_x'] ?? 0),
            'img_y'             => (float) ($validated['img_y'] ?? 0),
            'img_w'             => (float) ($validated['img_w'] ?? 100),
            'img_h'             => (float) ($validated['img_h'] ?? 100),
        ];

        try {
            $result = $this->exportService->export(
                $request->file('template'),
                $request->file('csv_file'),
                $config,
                $request->input('progress_id')
            );

            return response()->json([
                'success' => true,
                'download_url' => route('workspace.download', ['file' => $result['zip_name']])
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function progress($progressId)
    {
        $progress = cache()->get("progress_{$progressId}", 0);
        return response()->json(['progress' => (int) $progress]);
    }

    public function download($file)
    {
        $file = basename($file);
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($zipPath)) {
            abort(404, 'File not found');
        }

        Cookie::queue('download_started', 'true', 1, '/', null, false, false);

        return response()->download($zipPath, $file)->deleteFileAfterSend(true);
    }
}
