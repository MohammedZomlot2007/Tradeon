<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SmokeTest extends Command
{
    protected $signature = 'tradeon:smoke';

    protected $description = 'Smoke-test critical routes and services (used by uptime monitors and deploy checks)';

    public function handle(): int
    {
        $failures = 0;
        $check = function (string $label, callable $fn) use (&$failures) {
            try {
                $fn();
                $this->info("OK   {$label}");
            } catch (\Throwable $e) {
                $failures++;
                $this->error("FAIL {$label}: {$e->getMessage()}");
                logger()->error('tradeon:smoke failure', ['check' => $label, 'error' => $e->getMessage()]);
            }
        };

        $check('database connection', fn () => DB::select('select 1'));

        foreach (['landing', 'feed', 'sitemap', 'search', 'billing.pricing'] as $name) {
            $check("route {$name}", function () use ($name) {
                if (! Route::has($name)) {
                    throw new \RuntimeException('route missing');
                }
            });
        }

        $check('frontend build manifest', function () {
            if (! is_file(public_path('build/manifest.json'))) {
                throw new \RuntimeException('public/build/manifest.json missing — run npm run build');
            }
        });

        $check('storage link', function () {
            if (! file_exists(public_path('storage')) && ! is_link(public_path('storage'))) {
                throw new \RuntimeException('public/storage missing — run php artisan storage:link');
            }
        });

        if ($failures > 0) {
            $this->error("Smoke test failed ({$failures} checks).");

            return self::FAILURE;
        }

        $this->info('Smoke test passed.');

        return self::SUCCESS;
    }
}
