<?php

namespace App\Services;

use App\Models\Quote;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;

class QuotePDFService
{
    public function generate(Quote $quote): string
    {
        $quote->loadMissing(['client', 'items', 'user', 'user.companyProfile']);

        $html = view('filament.dashboard.components.quote-preview', [
            'quote'        => $quote,
            'template'     => $quote->template ?? 'modern',
            'primaryColor' => $quote->primary_color ?? '#3b82f6',
            'accentColor'  => $quote->accent_color ?? '#10b981',
        ])->render();

        $fullHtml = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quote ' . $quote->quote_number . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        @media print { body { margin: 0; padding: 0; } }
        body { margin: 0; padding: 0; background: white; }
    </style>
</head>
<body class="bg-white">
    ' . $html . '
</body>
</html>';

        $browsershot = Browsershot::html($fullHtml)
            ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
            ->format('A4')
            ->portrait()
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(120);

        $nodePath = $this->findNodePath();
        $npmPath  = $this->findNpmPath();

        if ($nodePath) {
            $browsershot->setNodeBinary($nodePath);
        }

        if ($npmPath) {
            $browsershot->setNpmBinary($npmPath);
        }

        $chromePath = $this->findChromePath();
        if ($chromePath) {
            $browsershot->setChromePath($chromePath);
        }

        return $browsershot->pdf();
    }

    public function download(Quote $quote): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($quote);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $quote->quote_number . '.pdf"',
        ]);
    }

    public function stream(Quote $quote): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($quote);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $quote->quote_number . '.pdf"',
        ]);
    }

    public function emailToClient(Quote $quote): bool
    {
        if (!$quote->client?->email) {
            return false;
        }

        try {
            $pdf = $this->generate($quote);

            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($quote, $pdf) {
                $message->to($quote->client->email, $quote->client->name)
                    ->subject('Quote ' . $quote->quote_number . ' from ' . ($quote->user->companyProfile?->company_name ?? $quote->user->name))
                    ->setBody(
                        'Dear ' . $quote->client->name . ",\n\nPlease find your quote attached.\n\nQuote Number: " . $quote->quote_number . "\nTotal: " . $quote->currency . ' ' . number_format($quote->total_amount, 2) . "\n\nThank you for your interest.",
                        'text/plain'
                    )
                    ->attachData($pdf, $quote->quote_number . '.pdf', ['mime' => 'application/pdf']);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Quote email error', ['quote_id' => $quote->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    protected function findNodePath(): ?string
    {
        if ($path = env('NODE_PATH')) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        $commonPaths = [
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/homebrew/bin/node',
            '/usr/local/node/bin/node',
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        $output = @shell_exec('which node 2>/dev/null');
        if ($output) {
            $path = trim($output);
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function findNpmPath(): ?string
    {
        if ($path = env('NPM_PATH')) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        $nodePath = $this->findNodePath();
        if ($nodePath) {
            $npmPath = dirname($nodePath) . '/npm';
            if (file_exists($npmPath) && is_executable($npmPath)) {
                return $npmPath;
            }
        }

        $commonPaths = [
            '/usr/bin/npm',
            '/usr/local/bin/npm',
            '/opt/homebrew/bin/npm',
            '/usr/local/node/bin/npm',
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        $output = @shell_exec('which npm 2>/dev/null');
        if ($output) {
            $path = trim($output);
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function findChromePath(): ?string
    {
        if ($path = env('CHROME_PATH')) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        $systemPaths = [
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
        ];

        foreach ($systemPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        $projectPath = base_path();
        $homeDir     = getenv('HOME') ?: '/root';

        $searchPaths = [
            $projectPath . '/node_modules',
            '/var/www/.cache',
            $homeDir . '/.cache',
            '/root/.cache',
        ];

        foreach ($searchPaths as $searchPath) {
            if (!is_dir($searchPath)) {
                continue;
            }

            $commands = [
                "find " . escapeshellarg($searchPath) . " -name 'chrome' -type f -path '*/chrome-linux*/chrome' 2>/dev/null | head -1",
                "find " . escapeshellarg($searchPath) . " -name 'chrome' -type f 2>/dev/null | grep -E 'chrome-linux.*chrome$' | head -1",
            ];

            foreach ($commands as $command) {
                $output = @shell_exec($command);
                if ($output) {
                    $path = trim($output);
                    if (file_exists($path) && is_executable($path)) {
                        return $path;
                    }
                }
            }
        }

        return null;
    }
}
