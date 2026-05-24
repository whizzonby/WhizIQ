<?php

namespace App\Services;

use App\Models\Receipt;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;

class ReceiptPDFService
{
    public function generate(Receipt $receipt): string
    {
        $receipt->loadMissing(['user', 'client']);

        $html = view('filament.dashboard.components.receipt-preview', [
            'receipt'       => $receipt,
            'template'      => $receipt->template ?? 'standard',
            'primaryColor'  => $receipt->primary_color ?? '#10b981',
            'forPdf'        => true,
        ])->render();

        $fullHtml = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt ' . $receipt->receipt_number . '</title>
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
            ->format($receipt->template === 'thermal' ? 'A6' : 'A4')
            ->portrait()
            ->margins(8, 8, 8, 8)
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

    public function download(Receipt $receipt): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($receipt);
        $filename = $receipt->receipt_number . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function stream(Receipt $receipt): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($receipt);
        $filename = $receipt->receipt_number . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
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
