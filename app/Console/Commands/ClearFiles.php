<?php

namespace App\Console\Commands;

use App\Models\Detail;
use App\Models\Film;
use App\Models\Screenshots;
use App\Models\File;
use Illuminate\Console\Command;

class ClearFiles extends Command
{
    protected $signature   = 'clear:film';
    protected $description = 'Clear extra film folder';

    public function handle()
    {
        $films = glob(public_path('storage/films/*'));
        foreach ($films as $film) {
            $film = Film::withTrashed()->where('uri', basename($film))->first();

            if (!$film)
                $this->recursiveRemoveDirectory($film);

            if ($film && $film->id) {
                $this->removeUnusedPreviewGrid($film);
                $this->removeUnusedPreviewDetail($film);
                $this->removeUnusedPreviewTrailer($film);
                $this->removeUnusedScreenshots($film);
                $this->removeUnusedfiles($film);
            }
        }
    }

    public function removeUnusedfiles($film)
    {
        $filesFolder = public_path('storage/films/' . $film->uri . '/files');
        $files = glob($filesFolder . '/*');

        foreach ($files as $file) {
            $filename = basename($file);
            $fileInDatabase = File::query()->where('film_id', $film->id)
                ->where('path', "films/$film->uri/file/$filename")
                ->first();

            if (!$fileInDatabase) {
                unlink($file);
                echo "Deleted unused file: $filename\n";
            }
        }
    }

    public function removeUnusedPreviewTrailer($film)
    {
        $previewTrailerFolder = public_path('storage/films/' . $film->uri . '/previewTrailer');
        $previewTrailer = glob($previewTrailerFolder . '/*');

        foreach ($previewTrailer as $preview) {
            $filename = basename($preview);
            $previewTrailerInDatabase = Detail::query()->where('id', $film->id)
                ->where('preview_trailer', "films/$film->uri/previewTrailer/$filename")
                ->first();

            if (!$previewTrailerInDatabase) {
                unlink($preview);
                echo "Deleted unused preview trailer: $filename\n";
            }
        }
    }

    public function removeUnusedPreviewDetail($film)
    {
        $previewDetailFolder = public_path('storage/films/' . $film->uri . '/previewDetail');
        $previewDetail = glob($previewDetailFolder . '/*');

        foreach ($previewDetail as $preview) {
            $filename = basename($preview);
            $previewDetailInDatabase = Detail::query()->where('id', $film->id)
                ->where('preview_detail', "films/$film->uri/previewDetail/$filename")
                ->first();

            if (!$previewDetailInDatabase) {
                unlink($preview);
                echo "Deleted unused preview detail: $filename\n";
            }
        }
    }

    public function removeUnusedPreviewGrid($film)
    {
        $previewGridFolder = public_path('storage/films/' . $film->uri . '/previewGrid');
        $previewGrid = glob($previewGridFolder . '/*');

        foreach ($previewGrid as $preview) {
            $filename = basename($preview);

            if ($film->preview_grid != "films/$film->uri/previewGrid/$filename") {
                unlink($preview);
                echo "Deleted unused preview grid: $filename\n";
            }
        }
    }

    public function removeUnusedScreenshots($film)
    {
        $screenshotFolder = public_path('storage/films/' . $film->uri . '/screenshots');
        $screenshots = glob($screenshotFolder . '/*');

        foreach ($screenshots as $screenshot) {
            $filename = basename($screenshot);
            $screenshotInDatabase = Screenshots::query()->where('film_id', $film->id)
                ->where('path', "films/$film->uri/screenshots/$filename")
                ->first();

            if (!$screenshotInDatabase) {
                unlink($screenshot);
                echo "Deleted unused screenshot: $filename\n";
            }
        }
    }

    private function recursiveRemoveDirectory($directoryPath)
    {
        if (is_dir($directoryPath)) {
            $objects = scandir($directoryPath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir( "$directoryPath/$object")) {
                        $this->recursiveRemoveDirectory("$directoryPath/$object");
                    } else {
                        unlink("$directoryPath/$object");
                        $this->info("Unlink $directoryPath/$object");
                    }
                }
            }
            rmdir($directoryPath);
        }
    }
}
