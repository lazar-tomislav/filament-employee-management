<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Pretvara XLSX datoteke u PDF pomoću LibreOffice headless moda.
 */
final class LibreOfficePdfConverter
{
    public function __construct(
        private readonly string $binary,
        private readonly int $timeout,
    ) {}

    public static function make(): self
    {
        return new self(
            (string) config('employee-management.monthly_report.libreoffice_path', 'soffice'),
            (int) config('employee-management.monthly_report.pdf_conversion_timeout', 120),
        );
    }

    /**
     * Provjerava je li LibreOffice binarka dostupna.
     */
    public function isAvailable(): bool
    {
        return $this->resolveBinary() !== null;
    }

    /**
     * Pretvara XLSX u PDF u istom direktoriju i vraća putanju do PDF-a.
     *
     * @throws RuntimeException
     */
    public function convert(string $xlsxPath): string
    {
        $binary = $this->resolveBinary();

        if ($binary === null) {
            throw new RuntimeException('LibreOffice nije instaliran ili putanja LIBREOFFICE_PATH nije ispravna.');
        }

        if (! is_file($xlsxPath)) {
            throw new RuntimeException("XLSX datoteka ne postoji: {$xlsxPath}");
        }

        $outputDir = dirname($xlsxPath);
        $profileDir = $outputDir . '/lo_profile_' . getmypid() . '_' . uniqid();
        File::ensureDirectoryExists($profileDir);

        $pdfPath = $outputDir . '/' . pathinfo($xlsxPath, PATHINFO_FILENAME) . '.pdf';

        try {
            $result = Process::timeout($this->timeout)
                ->env(['HOME' => getenv('HOME') ?: $profileDir])
                ->run([
                    $binary,
                    '--headless',
                    '--norestore',
                    '--nologo',
                    '-env:UserInstallation=file://' . $profileDir,
                    '--convert-to', 'pdf',
                    '--outdir', $outputDir,
                    $xlsxPath,
                ]);

            if (! $result->successful() || ! is_file($pdfPath)) {
                throw new RuntimeException(
                    'Pretvorba u PDF nije uspjela: ' . trim($result->errorOutput() ?: $result->output())
                );
            }
        } finally {
            File::deleteDirectory($profileDir);
        }

        return $pdfPath;
    }

    /**
     * Vraća apsolutnu putanju do binarke ili null ako nije pronađena.
     */
    private function resolveBinary(): ?string
    {
        if (str_contains($this->binary, '/')) {
            return is_executable($this->binary) ? $this->binary : null;
        }

        $result = Process::run(['which', $this->binary]);
        $path = trim($result->output());

        return $result->successful() && $path !== '' ? $path : null;
    }
}
