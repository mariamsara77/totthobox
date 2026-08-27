<?php

// app/Services/MediaConverterService.php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\MediaConversionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

final class MediaConverterService
{
    private const VIDEO_FORMATS = ['mp4', 'mkv', 'avi', 'mov', 'webm', 'flv', 'wmv'];

    private const AUDIO_FORMATS = ['mp3', 'wav', 'aac', 'flac', 'ogg', 'm4a', 'opus'];

    public function __construct(
        private readonly string $ffmpegBinary = 'ffmpeg',
        private readonly int $timeoutSeconds = 900,
    ) {}

    public static function isAudio(string $extension): bool
    {
        return in_array(strtolower($extension), self::AUDIO_FORMATS, true);
    }

    /** @return string[] */
    public static function availableTargets(string $extension): array
    {
        $ext = strtolower($extension);

        if (self::isAudio($ext)) {
            return array_values(array_diff(self::AUDIO_FORMATS, [$ext]));
        }

        return array_values(array_diff([...self::VIDEO_FORMATS, ...self::AUDIO_FORMATS], [$ext]));
    }

    public function convert(string $inputPath, string $outputDirectory, string $targetFormat): string
    {
        if (! File::isDirectory($outputDirectory)) {
            File::makeDirectory($outputDirectory, 0755, true);
        }

        $targetFormat = strtolower($targetFormat);
        $outputPath = rtrim($outputDirectory, '/').DIRECTORY_SEPARATOR
            .pathinfo($inputPath, PATHINFO_FILENAME).'.'.$targetFormat;

        $command = [
            $this->ffmpegBinary, '-y', '-i', $inputPath,
            ...$this->codecArgsFor($targetFormat),
            $outputPath,
        ];

        $result = Process::timeout($this->timeoutSeconds)->run($command);

        if ($result->failed()) {
            throw new MediaConversionException(
                sprintf('FFmpeg conversion failed: %s', trim($result->errorOutput() ?: $result->output()))
            );
        }

        if (! File::exists($outputPath)) {
            throw new MediaConversionException('Converted media file was not found after processing.');
        }

        return $outputPath;
    }

    /** Centralized codec/mux map — extend here only. */
    private function codecArgsFor(string $targetFormat): array
    {
        return match ($targetFormat) {
            'mp3' => ['-vn', '-acodec', 'libmp3lame', '-b:a', '192k'],
            'wav' => ['-vn', '-acodec', 'pcm_s16le'],
            'aac', 'm4a' => ['-vn', '-acodec', 'aac', '-b:a', '192k'],
            'ogg' => ['-vn', '-acodec', 'libvorbis'],
            'opus' => ['-vn', '-acodec', 'libopus'],
            'flac' => ['-vn', '-acodec', 'flac'],
            'mp4' => ['-c:v', 'libx264', '-preset', 'fast', '-crf', '23', '-c:a', 'aac', '-b:a', '128k', '-movflags', '+faststart'],
            'mkv' => ['-c:v', 'libx264', '-preset', 'fast', '-crf', '23', '-c:a', 'aac', '-b:a', '128k'],
            'avi' => ['-c:v', 'mpeg4', '-vtag', 'xvid', '-q:v', '5', '-c:a', 'libmp3lame', '-b:a', '128k'],
            'webm' => ['-c:v', 'libvpx-vp9', '-crf', '30', '-b:v', '0', '-c:a', 'libopus'],
            'mov' => ['-c:v', 'libx264', '-preset', 'fast', '-crf', '23', '-c:a', 'aac'],
            'flv' => ['-c:v', 'flv', '-c:a', 'libmp3lame'],
            'wmv' => ['-c:v', 'wmv2', '-c:a', 'wmav2'],
            default => [],
        };
    }
}
