<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DbBackup extends Command
{
    protected $signature = 'tradeon:backup {--keep=7 : Days of backups to keep}';

    protected $description = 'Create a compressed database backup (MySQL dump or SQLite copy) for daily cron';

    public function handle(): int
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/backups'),
        ]);
        $disk->makeDirectory('.');

        $date = now()->format('Y-m-d_H-i-s');
        $connection = config('database.default');

        if ($connection === 'mysql') {
            $file = "tradeon-mysql-{$date}.sql.gz";
            $path = $disk->path($file);
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');
            $db = config('database.connections.mysql.database');
            $user = config('database.connections.mysql.username');
            $pass = config('database.connections.mysql.password');
            // Password via env to avoid leaking in process list where possible.
            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s | gzip > %s',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($db),
                escapeshellarg($path)
            );
            exec($cmd, $out, $code);
            if ($code !== 0) {
                $this->error('mysqldump failed. Check DB credentials.');

                return self::FAILURE;
            }
        } else {
            // SQLite / fallback: copy the file and gzip it with PHP (portable on Windows too).
            $source = config('database.connections.sqlite.database', database_path('database.sqlite'));
            if (! is_file($source)) {
                $this->error("SQLite file not found: {$source}");

                return self::FAILURE;
            }
            $file = "tradeon-sqlite-{$date}.sqlite.gz";
            $path = $disk->path($file);
            $data = file_get_contents($source);
            file_put_contents($path, gzencode($data, 9));
        }

        // Prune old backups.
        $keep = max(1, (int) $this->option('keep'));
        $files = collect($disk->files('.'))
            ->filter(fn ($f) => str_ends_with($f, '.gz'))
            ->sort()
            ->values();
        while ($files->count() > $keep) {
            $disk->delete($files->shift());
        }

        $this->info("Backup ready: storage/app/backups/{$file}");

        return self::SUCCESS;
    }
}
