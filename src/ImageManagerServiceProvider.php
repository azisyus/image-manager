<?php


namespace Azizyus\ImageManager;


use Azizyus\ImageManager\Commands\GenerateVariations;
use Azizyus\ImageManager\DB\Models\ManagedImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Events\EventServiceProvider;

class ImageManagerServiceProvider extends EventServiceProvider
{

    public function register()
    {

    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/Views','ImageManager');
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        $this->commands([
            GenerateVariations::class,
        ]);

        $this->publishes([
            __DIR__.'/Vue' => resource_path('js')
        ],'managed-images');
    }

}
