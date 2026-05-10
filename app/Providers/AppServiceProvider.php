<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('database.default') !== 'mysql') {
            throw new \RuntimeException(
                'Aplikasi ini WAJIB menggunakan MySQL. ' .
                'DB_CONNECTION saat ini: ' . config('database.default')
            );
        }
    }
}
