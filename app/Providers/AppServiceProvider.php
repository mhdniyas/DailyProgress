<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }


public function boot(): void
{
    FilamentAsset::register([
        Js::make('apexcharts', 'https://cdn.jsdelivr.net/npm/apexcharts')->async(),
    ]);
}

}
