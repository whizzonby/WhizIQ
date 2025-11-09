<?php

namespace App\Http\Controllers;

use App\Services\RevenueSourceExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class RevenueSourceExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function download(Request $request)
    {
        $request->validate([
            'format' => 'sometimes|in:csv,excel',
            'from_date' => 'sometimes|date',
            'until_date' => 'sometimes|date|after_or_equal:from_date',
            'source' => 'sometimes|array',
        ]);

        $service = app(RevenueSourceExportService::class);
        $format = $request->input('format', 'csv');

        $filters = [];
        if ($request->has('from_date')) {
            $filters['from_date'] = $request->input('from_date');
        }
        if ($request->has('until_date')) {
            $filters['until_date'] = $request->input('until_date');
        }
        if ($request->has('source')) {
            $source = $request->input('source');
            // Handle both array and single value
            if (is_array($source)) {
                $filters['source'] = $source;
            } elseif (!empty($source)) {
                $filters['source'] = [$source];
            }
        }

        if ($format === 'excel') {
            $content = $service->exportToExcel(auth()->id(), $filters);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } else {
            $content = $service->exportToCsv(auth()->id(), $filters);
            $contentType = 'text/csv';
        }

        // Check if content is empty
        if (empty($content) || trim($content) === '') {
            abort(404, 'No revenue sources found matching your criteria.');
        }

        $filename = $service->getExportFilename($format);

        return Response::streamDownload(function () use ($content) {
            echo $content;
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, $filename, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

