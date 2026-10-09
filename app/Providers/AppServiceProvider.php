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
        // Tahun ajaran SPMB global — bisa diganti admin di Pengaturan,
        // otomatis tampil di seluruh situs (layout, landing, PDF, email).
        try {
            $tahunAjaran = \App\Models\Pengaturan::get('tahun_ajaran', '2027/2028');
        } catch (\Throwable $e) {
            $tahunAjaran = '2027/2028';
        }

        \Illuminate\Support\Facades\View::share('tahunAjaran', $tahunAjaran);
    }
}
