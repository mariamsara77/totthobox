<?php

// app/Services/DocumentConverterService.php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DocumentConversionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

final class DocumentConverterService
{
    /** Extension → family. LibreOffice can only meaningfully convert within a family (or to/from PDF). */
    private const FAMILY_MAP = [
        'doc' => 'word', 'docx' => 'word', 'odt' => 'word', 'rtf' => 'word', 'txt' => 'word',
        'xls' => 'sheet', 'xlsx' => 'sheet', 'ods' => 'sheet', 'csv' => 'sheet',
        'ppt' => 'slide', 'pptx' => 'slide', 'odp' => 'slide',
        'pdf' => 'universal',
    ];

    private const TARGETS_BY_FAMILY = [
        'word' => ['pdf', 'docx', 'odt', 'rtf', 'txt'],
        'sheet' => ['pdf', 'xlsx', 'ods', 'csv'],
        'slide' => ['pdf', 'pptx', 'odp'],
        'universal' => ['docx', 'odt', 'pptx', 'xlsx'],
    ];

    /** Some LibreOffice export filters need to be explicit, otherwise conversion picks a wrong default. */
    private const EXPORT_FILTERS = [
        'csv' => 'csv:Text - txt - csv (StarCalc)',
        'txt' => 'txt:Text (encoded):UTF8',
    ];

    public function __construct(
        private readonly string $sofficeBinary = 'soffice',
        private readonly int $timeoutSeconds = 180,
    ) {}

    public static function familyFor(string $extension): string
    {
        return self::FAMILY_MAP[strtolower($extension)] ?? 'word';
    }

    /** @return string[] */
    public static function availableTargets(string $extension): array
    {
        $family = self::familyFor($extension);
        $targets = self::TARGETS_BY_FAMILY[$family];

        return array_values(array_diff($targets, [strtolower($extension)]));
    }

    public function convert(string $inputPath, string $outputDirectory, string $targetFormat): string
    {
        if (! File::isDirectory($outputDirectory)) {
            File::makeDirectory($outputDirectory, 0755, true);
        }

        $convertToArg = self::EXPORT_FILTERS[$targetFormat] ?? $targetFormat;

        $result = Process::timeout($this->timeoutSeconds)
            ->path($outputDirectory)
            ->run([
                $this->sofficeBinary,
                '--headless',
                '--norestore',
                '--convert-to', $convertToArg,
                '--outdir', $outputDirectory,
                $inputPath,
            ]);

        if ($result->failed()) {
            throw new DocumentConversionException(
                sprintf('LibreOffice conversion failed: %s', trim($result->errorOutput() ?: $result->output()))
            );
        }

        $outputPath = rtrim($outputDirectory, '/').DIRECTORY_SEPARATOR
            .pathinfo($inputPath, PATHINFO_FILENAME).'.'.$targetFormat;

        if (! File::exists($outputPath)) {
            throw new DocumentConversionException('Converted document was not found after processing.');
        }

        return $outputPath;
    }
}
