<?php

namespace App\Console\Commands;

use App\Models\Detail;
use App\Models\Game;
use App\Models\Screenshots;
use App\Models\Torrents;
use Illuminate\Console\Command;

class ClearFiles extends Command
{
    protected $signature   = 'clear:game';
    protected $description = 'Clear extra game folder';

    public function handle()
    {
        $games = glob(public_path('storage/games/*'));
        foreach ($games as $game) {
            $game = Game::withTrashed()->where('uri', basename($game))->first();

            if (!$game)
                $this->recursiveRemoveDirectory($game);

            if ($game && $game->id) {
                $this->removeUnusedPreviewGrid($game);
                $this->removeUnusedPreviewDetail($game);
                $this->removeUnusedPreviewTrailer($game);
                $this->removeUnusedScreenshots($game);
                $this->removeUnusedTorrents($game);
            }
        }
    }

    public function removeUnusedTorrents($game)
    {
        $torrentsFolder = public_path('storage/games/' . $game->uri . '/torrent');
        $torrents = glob($torrentsFolder . '/*');

        foreach ($torrents as $torrent) {
            $filename = basename($torrent);
            $torrentInDatabase = Torrents::where('game_id', $game->id)
                ->where('path', "games/$game->uri/torrent/$filename")
                ->first();

            if (!$torrentInDatabase) {
                unlink($torrent);
                echo "Deleted unused torrent: $filename\n";
            }
        }
    }

    public function removeUnusedPreviewTrailer($game)
    {
        $previewTrailerFolder = public_path('storage/games/' . $game->uri . '/previewTrailer');
        $previewTrailer = glob($previewTrailerFolder . '/*');

        foreach ($previewTrailer as $preview) {
            $filename = basename($preview);
            $previewTrailerInDatabase = Detail::where('id', $game->id)
                ->where('preview_trailer', "games/$game->uri/previewTrailer/$filename")
                ->first();

            if (!$previewTrailerInDatabase) {
                unlink($preview);
                echo "Deleted unused preview trailer: $filename\n";
            }
        }
    }

    public function removeUnusedPreviewDetail($game)
    {
        $previewDetailFolder = public_path('storage/games/' . $game->uri . '/previewDetail');
        $previewDetail = glob($previewDetailFolder . '/*');

        foreach ($previewDetail as $preview) {
            $filename = basename($preview);
            $previewDetailInDatabase = Detail::where('id', $game->id)
                ->where('preview_detail', "games/$game->uri/previewDetail/$filename")
                ->first();

            if (!$previewDetailInDatabase) {
                unlink($preview);
                echo "Deleted unused preview detail: $filename\n";
            }
        }
    }

    public function removeUnusedPreviewGrid($game)
    {
        $previewGridFolder = public_path('storage/games/' . $game->uri . '/previewGrid');
        $previewGrid = glob($previewGridFolder . '/*');

        foreach ($previewGrid as $preview) {
            $filename = basename($preview);

            if ($game->preview_grid != "games/$game->uri/previewGrid/$filename") {
                unlink($preview);
                echo "Deleted unused preview grid: $filename\n";
            }
        }
    }

    public function removeUnusedScreenshots($game)
    {
        $screenshotFolder = public_path('storage/games/' . $game->uri . '/screenshots');
        $screenshots = glob($screenshotFolder . '/*');

        foreach ($screenshots as $screenshot) {
            $filename = basename($screenshot);
            $screenshotInDatabase = Screenshots::where('game_id', $game->id)
                ->where('path', "games/$game->uri/screenshots/$filename")
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
