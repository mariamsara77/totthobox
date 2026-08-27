<?php

// app/Services/DataConverterService.php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DataConversionException;
use SimpleXMLElement;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final class DataConverterService
{
    private const FORMATS = ['json', 'xml', 'yaml', 'csv'];

    public static function normalize(string $extension): string
    {
        $ext = strtolower($extension);

        return $ext === 'yml' ? 'yaml' : $ext;
    }

    /** @return string[] */
    public static function availableTargets(string $extension): array
    {
        return array_values(array_diff(self::FORMATS, [self::normalize($extension)]));
    }

    public function convert(string $content, string $sourceFormat, string $targetFormat): string
    {
        try {
            $data = $this->parse($content, self::normalize($sourceFormat));

            return $this->encode($data, self::normalize($targetFormat));
        } catch (Throwable $e) {
            throw new DataConversionException("Conversion failed: {$e->getMessage()}", previous: $e);
        }
    }

    private function parse(string $content, string $format): array
    {
        return match ($format) {
            'json' => (array) json_decode($content, true, flags: JSON_THROW_ON_ERROR),
            'yaml' => (array) Yaml::parse($content),
            'xml' => $this->xmlToArray(new SimpleXMLElement($content)),
            'csv' => $this->csvToArray($content),
            default => throw new DataConversionException("Unsupported source format: {$format}"),
        };
    }

    private function encode(array $data, string $format): string
    {
        return match ($format) {
            'json' => (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'yaml' => Yaml::dump($data, 6, 2),
            'xml' => $this->arrayToXml($data),
            'csv' => $this->arrayToCsv($data),
            default => throw new DataConversionException("Unsupported target format: {$format}"),
        };
    }

    private function xmlToArray(SimpleXMLElement $xml): array
    {
        return (array) json_decode((string) json_encode($xml), true);
    }

    private function arrayToXml(array $data, string $root = 'root'): string
    {
        $xml = new SimpleXMLElement("<{$root}/>");
        $this->arrayToXmlRecursive($data, $xml);

        return (string) $xml->asXML();
    }

    private function arrayToXmlRecursive(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            $key = is_numeric($key) ? 'item'.$key : preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $key);

            if (is_array($value)) {
                $child = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $child);

                continue;
            }

            $xml->addChild($key, htmlspecialchars((string) $value));
        }
    }

    private function csvToArray(string $content): array
    {
        $rows = array_map('str_getcsv', preg_split('/\r\n|\r|\n/', trim($content)));
        $header = array_shift($rows);

        return array_values(array_map(
            fn (array $row) => array_combine($header, array_pad($row, count($header), null)),
            array_filter($rows)
        ));
    }

    private function arrayToCsv(array $data): string
    {
        if ($data === []) {
            return '';
        }

        $rows = array_is_list($data) ? $data : [$data];
        $header = array_keys((array) reset($rows));

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $header);

        foreach ($rows as $row) {
            fputcsv($stream, array_map(
                fn ($v) => is_array($v) ? json_encode($v) : $v,
                (array) $row
            ));
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }
}
