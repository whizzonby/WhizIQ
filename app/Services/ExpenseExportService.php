<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExpenseExportService
{
    /**
     * Export expenses to CSV
     */
    public function exportToCsv(int $userId, ?array $filters = null): string
    {
        $query = Expense::where('user_id', $userId);

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
            if (isset($filters['category']) && !empty($filters['category'])) {
                if (is_array($filters['category'])) {
                    $query->whereIn('category', $filters['category']);
                } else {
                    $query->where('category', $filters['category']);
                }
            }
            if (isset($filters['is_tax_deductible']) && $filters['is_tax_deductible'] !== null && $filters['is_tax_deductible'] !== '') {
                $query->where('is_tax_deductible', (bool) $filters['is_tax_deductible']);
            }
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        return $this->generateCsv($expenses);
    }

    /**
     * Generate CSV content from expenses
     */
    protected function generateCsv(Collection $expenses): string
    {
        // If no expenses, return empty to trigger notification
        if ($expenses->isEmpty()) {
            return '';
        }

        $csv = [];

        // Header row
        $csv[] = [
            'Date',
            'Category',
            'Amount',
            'Description',
            'Tax Deductible',
            'Deductible Amount',
            'Tax Category',
            'Tax Notes',
            'Created At',
        ];

        // Data rows
        foreach ($expenses as $expense) {
            $csv[] = [
                $expense->date?->format('Y-m-d') ?? '',
                $expense->category ?? '',
                $expense->amount ? number_format($expense->amount, 2, '.', '') : '0.00',
                $expense->description ?? '',
                $expense->is_tax_deductible ? 'Yes' : 'No',
                $expense->deductible_amount ? number_format($expense->deductible_amount, 2, '.', '') : '',
                $expense->taxCategory?->name ?? '',
                $expense->tax_notes ?? '',
                $expense->created_at?->format('Y-m-d H:i:s') ?? '',
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
     * Export expenses to Excel (CSV format for now, can be extended with PhpSpreadsheet)
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
        return 'expenses_export_' . date('Y-m-d_His') . '.' . $extension;
    }
}



