<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DocumentConversionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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
        $targets = self::TARGETS_BY_FAMILY[$family] ?? [];

        return array_values(array_diff($targets, [strtolower($extension)]));
    }

    public function convert(string $inputPath, string $outputDirectory, string $targetFormat): string
    {
        if (! File::isDirectory($outputDirectory)) {
            File::makeDirectory($outputDirectory, 0755, true);
        }

        $sourceFormat = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));

        // যেকোনো ডকুমেন্ট থেকে PDF কনভার্ট করতে লোকাল LibreOffice ব্যবহার হবে
        if ($targetFormat === 'pdf' && $sourceFormat !== 'pdf') {
            return $this->convertWithLibreOffice($inputPath, $outputDirectory, $targetFormat);
        }

        // PDF থেকে DOCX/XLSX/PPTX ইত্যাদি কনভার্ট করতে CloudConvert API ব্যবহার হবে
        return $this->convertWithCloudConvert($inputPath, $outputDirectory, $targetFormat);
    }

    private function convertWithLibreOffice(string $inputPath, string $outputDirectory, string $targetFormat): string
    {
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
            throw new DocumentConversionException('Converted document was not found after LibreOffice processing.');
        }

        return $outputPath;
    }

    private function convertWithCloudConvert(string $inputPath, string $outputDirectory, string $targetFormat): string
    {
        $apiKey = config('services.cloudconvert.api_key');

        if (! $apiKey) {
            throw new DocumentConversionException('CloudConvert API key is missing in config/services.php.');
        }

        $baseUrl = 'https://api.cloudconvert.com/v2';

        // ১. CloudConvert Job তৈরি
        $jobResponse = Http::withToken($apiKey)->post("{$baseUrl}/jobs", [
            'tasks' => [
                'import-file' => [
                    'operation' => 'import/upload',
                ],
                'convert-file' => [
                    'operation' => 'convert',
                    'input' => 'import-file',
                    'output_format' => $targetFormat,
                ],
                'export-file' => [
                    'operation' => 'export/url',
                    'input' => 'convert-file',
                ],
            ],
        ]);

        if ($jobResponse->failed()) {
            throw new DocumentConversionException('CloudConvert Job Creation Failed: '.$jobResponse->body());
        }

        $jobData = $jobResponse->json('data');
        $uploadTask = collect($jobData['tasks'])->firstWhere('name', 'import-file');

        // ২. ফাইল আপলোড
        $uploadUrl = $uploadTask['result']['form']['url'];
        $uploadParams = $uploadTask['result']['form']['parameters'];

        $uploadRequest = Http::asMultipart();
        foreach ($uploadParams as $key => $value) {
            $uploadRequest->attach($key, (string) $value);
        }
        $uploadResponse = $uploadRequest
            ->attach('file', file_get_contents($inputPath), pathinfo($inputPath, PATHINFO_BASENAME))
            ->post($uploadUrl);

        if ($uploadResponse->failed()) {
            throw new DocumentConversionException('CloudConvert File Upload Failed.');
        }

        // ৩. প্রসেস শেষ হওয়া পর্যন্ত অপেক্ষা (Wait)
        $waitResponse = Http::withToken($apiKey)
            ->timeout($this->timeoutSeconds)
            ->get("{$baseUrl}/jobs/{$jobData['id']}/wait");

        if ($waitResponse->failed()) {
            throw new DocumentConversionException('CloudConvert Processing Failed or Timed Out.');
        }

        $completedTasks = $waitResponse->json('data.tasks');
        $exportTask = collect($completedTasks)->firstWhere('name', 'export-file');

        if (! isset($exportTask['result']['files'][0]['url'])) {
            throw new DocumentConversionException('CloudConvert failed to return converted file URL.');
        }

        // ৪. কনভার্ট হওয়া ফাইল ডাউনলোড করে আউটপুট ডিরেক্টরি-তে সেভ করা
        $downloadUrl = $exportTask['result']['files'][0]['url'];
        $outputPath = rtrim($outputDirectory, '/').DIRECTORY_SEPARATOR
            .pathinfo($inputPath, PATHINFO_FILENAME).'.'.$targetFormat;

        $fileContent = Http::get($downloadUrl)->body();
        File::put($outputPath, $fileContent);

        return $outputPath;
    }
}
