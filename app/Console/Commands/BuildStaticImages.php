<?php

namespace App\Console\Commands;

use App\Services\StaticImageService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('images:build-static {--force : Rebuild derivatives even when valid files already exist}')]
#[Description('Build WebP derivatives for the static site photographs and marks.')]
class BuildStaticImages extends Command
{
    public function handle(StaticImageService $images): int
    {
        $sourceDir = config('images.static.source_dir');
        $markSourceDir = config('images.static.mark_source_dir');
        $photos = config('images.static.photos');
        $marks = config('images.static.marks', []);
        $markWidths = config('images.static.mark_widths', []);
        $force = (bool) $this->option('force');

        if (! is_string($sourceDir) || ! is_dir($sourceDir)) {
            $this->error('Static image source directory is missing.');

            return self::FAILURE;
        }

        if (! is_string($markSourceDir) || ! is_dir($markSourceDir)) {
            $this->error('Static mark source directory is missing.');

            return self::FAILURE;
        }

        if (! is_array($photos) || $photos === []) {
            $this->error('No static images are configured.');

            return self::FAILURE;
        }

        if (! is_array($marks) || $marks === []) {
            $this->error('No static marks are configured.');

            return self::FAILURE;
        }

        $sourceBytes = 0;
        $outputBytes = 0;
        $written = 0;
        $skipped = 0;
        $convertedImages = [];

        foreach ($photos as $name => $filename) {
            if (! is_string($name) || ! is_string($filename)) {
                $this->error('Static image configuration is invalid.');

                return self::FAILURE;
            }

            $result = $images->convert($name, $sourceDir.DIRECTORY_SEPARATOR.$filename, $force);
            $convertedImages[] = $result;
            $sourceBytes += $result['source_bytes'];

            foreach ($result['derivatives'] as $derivative) {
                $outputBytes += $derivative['bytes'];

                if ($derivative['status'] === 'written') {
                    $written++;
                } else {
                    $skipped++;
                }
            }

            $this->line(sprintf(
                '%s: %dx%d source, %d derivatives',
                $name,
                $result['original_width'],
                $result['original_height'],
                count($result['derivatives']),
            ));
        }

        foreach ($marks as $name => $filename) {
            if (! is_string($name) || ! is_string($filename)) {
                $this->error('Static mark configuration is invalid.');

                return self::FAILURE;
            }

            $result = $images->convert($name, $markSourceDir.DIRECTORY_SEPARATOR.$filename, $force, $markWidths);
            $convertedImages[] = $result;
            $sourceBytes += $result['source_bytes'];

            foreach ($result['derivatives'] as $derivative) {
                $outputBytes += $derivative['bytes'];

                if ($derivative['status'] === 'written') {
                    $written++;
                } else {
                    $skipped++;
                }
            }

            $this->line(sprintf(
                '%s: %dx%d source, %d derivatives',
                $name,
                $result['original_width'],
                $result['original_height'],
                count($result['derivatives']),
            ));
        }

        $manifestPath = $images->writeManifest($convertedImages);
        $this->line("Manifest: {$manifestPath}");

        $this->info(sprintf(
            'Done. %d source bytes, %d output bytes, %d written, %d skipped.',
            $sourceBytes,
            $outputBytes,
            $written,
            $skipped,
        ));

        return self::SUCCESS;
    }
}
