<?php

namespace App\Filament\Dashboard\Resources\BusinessMetricResource\Pages;

use App\Filament\Dashboard\Resources\BusinessMetricResource;
use App\Services\BusinessMetricAggregationService;
use App\Services\BusinessMetricExportService;
use App\Services\BusinessMetricImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;

class ListBusinessMetrics extends ListRecords
{
    protected static string $resource = BusinessMetricResource::class;

    public function getSubheading(): ?string
    {
        return 'This data is automatically calculated from your revenue sources, client payments, and expenses. Metrics are updated daily at 12:05 AM, or you can refresh them manually using the button above.';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Removed import and template actions - metrics are auto-calculated, not manually imported

            Action::make('export')
                ->label('Export Metrics')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->form([
                    Forms\Components\DatePicker::make('from_date')
                        ->label('From Date')
                        ->helperText('Export metrics from this date onwards'),

                    Forms\Components\DatePicker::make('until_date')
                        ->label('Until Date')
                        ->helperText('Export metrics up to this date'),

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
                    $service = app(BusinessMetricExportService::class);
                    $format = $data['format'] ?? 'csv';

                    $filters = [];
                    if (isset($data['from_date'])) {
                        $filters['from_date'] = $data['from_date'];
                    }
                    if (isset($data['until_date'])) {
                        $filters['until_date'] = $data['until_date'];
                    }

                    if ($format === 'excel') {
                        $content = $service->exportToExcel(auth()->id(), $filters);
                        $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    } else {
                        $content = $service->exportToCsv(auth()->id(), $filters);
                        $contentType = 'text/csv';
                    }

                    $filename = $service->getExportFilename($format);

                    return Response::streamDownload(function () use ($content) {
                        echo $content;
                    }, $filename, [
                        'Content-Type' => $contentType,
                    ]);
                }),

            Action::make('refresh')
                ->label('Refresh Metrics')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Refresh Business Metrics')
                ->modalDescription('This will recalculate all your business metrics from your revenue and expense data. This may take a few moments.')
                ->modalSubmitActionLabel('Yes, Refresh Now')
                ->action(function () {
                    try {
                        $service = app(BusinessMetricAggregationService::class);

                        // Recalculate metrics for the last 90 days
                        $service->aggregateForDateRange(
                            auth()->id(),
                            now()->subDays(90),
                            now()
                        );

                        Notification::make()
                            ->title('Metrics Refreshed')
                            ->success()
                            ->body('Your business metrics have been successfully recalculated from your revenue and expense data.')
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Refresh Failed')
                            ->danger()
                            ->body('Failed to refresh metrics: ' . $e->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
