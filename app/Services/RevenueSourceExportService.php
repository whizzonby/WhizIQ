<?php

namespace App\Services;

use App\Models\RevenueSource;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueSourceExportService
{
    /**
     * Export revenue sources to CSV
     */
    public function exportToCsv(int $userId, ?array $filters = null): string
    {
        $query = RevenueSource::where('user_id', $userId);

        // Apply filters if provided
        if ($filters) {
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                $fromDate = is_string($filters['from_date']) 
                    ? Carbon::parse($filters['from_date'])->startOfDay()
                    : $filters['from_date'];
                $query->whereDate('date', '>=', $fromDate);
            }
            if (isset($filters['until_date']) && !empty($filters['until_date'])) {
                $untilDate = is_string($filters['until_date']) 
                    ? Carbon::parse($filters['until_date'])->endOfDay()
                    : $filters['until_date'];
                $query->whereDate('date', '<=', $untilDate);
            }
            if (isset($filters['source']) && !empty($filters['source'])) {
                if (is_array($filters['source'])) {
                    $query->whereIn('source', $filters['source']);
                } else {
                    $query->where('source', $filters['source']);
                }
            }
        }

        $revenueSources = $query->orderBy('date', 'desc')->get();

        return $this->generateCsv($revenueSources);
    }

    /**
     * Generate CSV content from revenue sources
     */
    protected function generateCsv(Collection $revenueSources): string
    {
        // If no revenue sources, return empty to trigger notification
        if ($revenueSources->isEmpty()) {
            return '';
        }

        $csv = [];

        // Header row
        $csv[] = [
            'Date',
            'Source',
            'Amount',
            'Percentage',
            'Created At',
        ];

        // Data rows
        foreach ($revenueSources as $revenueSource) {
            $csv[] = [
                $revenueSource->date?->format('Y-m-d') ?? '',
                $revenueSource->source ?? '',
                $revenueSource->amount ? number_format($revenueSource->amount, 2, '.', '') : '0.00',
                $revenueSource->percentage ? number_format($revenueSource->percentage, 2, '.', '') : '',
                $revenueSource->created_at?->format('Y-m-d H:i:s') ?? '',
            ];
        }

        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        if ($output === false) {
            return '';
        }

        foreach ($csv as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // Ensure we have valid content (at least headers)
        if (empty($csvContent) || strlen(trim($csvContent)) === 0) {
            return '';
        }

        return $csvContent;
    }

    /**
     * Export revenue sources to Excel (CSV format for now, can be extended with PhpSpreadsheet)
     */
    public function exportToExcel(int $userId, ?array $filters = null): string
    {
        // For now, return CSV (can be extended to use PhpSpreadsheet for real Excel)
        return $this->exportToCsv($userId, $filters);
    }

    /**
     * Get filename for export
     */
    public function getExportFilename(string $format = 'csv'): string
    {
        $extension = $format === 'excel' ? 'xlsx' : 'csv';
        return 'revenue_sources_export_' . date('Y-m-d_His') . '.' . $extension;
    }
}

