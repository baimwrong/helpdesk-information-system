<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkImportLog;
use App\Services\BulkImportService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BulkImportController extends Controller
{
    public function __construct(
        protected BulkImportService $importService
    ) {}

    /**
     * Display the Bulk Import center and audit log.
     */
    public function index(): View
    {
        $logs = BulkImportLog::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.bulk-import.index', compact('logs'));
    }

    /**
     * Handle CSV bulk import.
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'in:users,tickets,departments,categories,articles'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'], // max 20MB
        ]);

        $admin = Auth::user();

        try {
            $result = $this->importService->import(
                $validated['entity_type'],
                $request->file('csv_file'),
                $admin
            );

            $message = "Import completed! Successfully imported {$result['imported']} rows.";
            if ($result['failed'] > 0) {
                $message .= " {$result['failed']} rows encountered errors (see import log below).";

                return back()->with('warning', $message);
            }

            return back()->with('success', $message);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Import failed: '.$e->getMessage()]);
        }
    }

    /**
     * Download a sample CSV template for the requested entity.
     */
    public function downloadTemplate(string $type): Response
    {
        if (! in_array($type, ['users', 'tickets', 'departments', 'categories', 'articles'], true)) {
            abort(404, 'Template not found');
        }

        $content = $this->importService->getCsvTemplate($type);
        $fileName = "sample_import_{$type}_template.csv";

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
