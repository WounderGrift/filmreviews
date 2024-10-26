<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDatabase extends Command
{
    protected $signature   = 'ms:refresh';
    protected $description = 'Refresh the database and clear the storage directory';

    public function handle()
    {
        $this->call('migrate:refresh');
        $files = glob(public_path('storage/avatars/*'));
        foreach ($files as $file) {
            if (is_file($file))
                unlink($file);
        }

        $files = glob(public_path('storage/banners/*'));
        foreach ($files as $file) {
            if (is_file($file))
                unlink($file);
        }

        $baseDirectory = storage_path("app/public/films");
        if (is_dir($baseDirectory))
            $this->recursiveRemoveDirectory($baseDirectory);

        if (file_exists(public_path('sitemap.xml')) && file_exists(glob(public_path('sitemap.xml'))[0]))
            unlink(glob(public_path('sitemap.xml'))[0]);
        $this->info('Database refreshed and storage directory cleared successfully.');
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
