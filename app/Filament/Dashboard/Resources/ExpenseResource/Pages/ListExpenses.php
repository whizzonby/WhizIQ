<?php

namespace App\Filament\Dashboard\Resources\ExpenseResource\Pages;

use App\Filament\Dashboard\Resources\ExpenseResource;
use App\Filament\Dashboard\Resources\ExpenseResource\Widgets\ExpenseCategoryBreakdownWidget;
use App\Filament\Dashboard\Resources\ExpenseResource\Widgets\ExpenseMonthlyTrendWidget;
use App\Services\ExpenseExportService;
use App\Services\ExpenseImportService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Response;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Expenses')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('CSV/Excel File')
                        ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required()
                        ->helperText('Upload a CSV or Excel file with expense data')
                        ->maxSize(5120), // 5MB
                ])
                ->action(function (array $data) {
                    try {
                        $service = app(ExpenseImportService::class);

                        // Get file content
                        $filePath = storage_path('app/public/' . $data['file']);

                        if (!file_exists($filePath)) {
                            Notification::make()
                                ->title('File Not Found')
                                ->danger()
                                ->body('The uploaded file could not be found.')
                                ->send();
                            return;
                        }

                        $csvContent = file_get_contents($filePath);

                        // Validate structure
                        $validation = $service->validateCsvStructure($csvContent);
                        if (!$validation['valid']) {
                            Notification::make()
                                ->title('Invalid CSV')
                                ->danger()
                                ->body($validation['message'])
                                ->send();
                            return;
                        }

                        // Import
                        $results = $service->importFromCsv($csvContent, auth()->id());

                        // Show results
                        $message = "Successfully imported {$results['success']} expenses.";
                        if ($results['failed'] > 0) {
                            $message .= " {$results['failed']} failed.";
                        }

                        Notification::make()
                            ->title('Import Complete')
                            ->success()
                            ->body($message)
                            ->send();

                        if (!empty($results['errors'])) {
                            foreach (array_slice($results['errors'], 0, 5) as $error) {
                                Notification::make()
                                    ->title('Import Error')
                                    ->warning()
                                    ->body($error)
                                    ->send();
                            }
                        }

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Failed')
                            ->danger()
                            ->body($e->getMessage())
                            ->send();
                    }
                }),

            Action::make('download_template')
                ->label('Download CSV Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $service = app(ExpenseImportService::class);
                    $template = $service->getCsvTemplate();

                    return Response::streamDownload(function () use ($template) {
                        echo $template;
                    }, 'expenses_import_template.csv', [
                        'Content-Type' => 'text/csv',
                    ]);
                }),

            Action::make('export')
                ->label('Export Expenses')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->form([
                    Forms\Components\DatePicker::make('from_date')
                        ->label('From Date')
                        ->helperText('Export expenses from this date onwards'),

                    Forms\Components\DatePicker::make('until_date')
                        ->label('Until Date')
                        ->helperText('Export expenses up to this date'),

                    Forms\Components\Select::make('category')
                        ->label('Filter by Category')
                        ->options(function () {
                            return \App\Models\Expense::where('user_id', auth()->id())
                                ->distinct()
                                ->pluck('category', 'category')
                                ->toArray();
                        })
                        ->multiple()
                        ->searchable()
                        ->placeholder('All categories'),

                    Forms\Components\Select::make('is_tax_deductible')
                        ->label('Tax Deductible')
                        ->options([
                            '1' => 'Deductible only',
                            '0' => 'Non-deductible only',
                        ])
                        ->placeholder('All expenses'),

                    Forms\Components\Select::make('format')
                        ->label('Export Format')
                        ->options([
                            'csv' => 'CSV',
                            'excel' => 'Excel (CSV)',
                        ])
                        ->default('csv')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $format = $data['format'] ?? 'csv';

                        // Build query parameters for the download route
                        $params = ['format' => $format];

                        if (isset($data['from_date']) && !empty($data['from_date'])) {
                            $params['from_date'] = is_string($data['from_date']) ? $data['from_date'] : $data['from_date']->format('Y-m-d');
                        }
                        if (isset($data['until_date']) && !empty($data['until_date'])) {
                            $params['until_date'] = is_string($data['until_date']) ? $data['until_date'] : $data['until_date']->format('Y-m-d');
                        }
                        if (isset($data['category']) && !empty($data['category'])) {
                            $params['category'] = is_array($data['category']) ? $data['category'] : [$data['category']];
                        }
                        if (isset($data['is_tax_deductible']) && $data['is_tax_deductible'] !== null && $data['is_tax_deductible'] !== '') {
                            $params['is_tax_deductible'] = (bool) $data['is_tax_deductible'];
                        }

                        // Build the download URL with proper query string
                        $url = route('expenses.export') . '?' . http_build_query($params);

                        // Redirect to download route - browser will handle the download
                        return redirect($url);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Export Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExpenseCategoryBreakdownWidget::class,
            ExpenseMonthlyTrendWidget::class,
        ];
    }
}
