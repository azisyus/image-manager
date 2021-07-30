<?php


namespace Azizyus\ImageManager;


use Azizyus\ImageManager\Commands\GenerateVariations;
use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\DB\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageManagerServiceProvider extends EventServiceProvider
{

    public function register()
    {
        $this->app->singleton('imageManager',function(){
            $s = new Manager(Storage::disk('public'));
            $s->setDeleteUrl('/delete');
            $s->setUploadUrl('/upload');
            $s->setFilesUrl('/files');
            $s->setSortUrl('/sort');
            $s->setCropFilesUrl('/crop');
            $s->setRemoteUrlUploadUrl('/remote');
            $s->setSpecialImagesUrl('/specialImages');
            $s->setChooseSpecialImageUrl('/chooseSpecialImage');
            return $s;
        });
    }

    public function boot()
    {
        ManagedImage::created(function(ManagedImage $image){
            imageManager()->maintainVariations($image->fileName);
        });
        ManagedImage::updated(function(ManagedImage $image){
            if($image->wasChanged('fileName'))
                imageManager()->maintainVariations($image->fileName);
        });

        ManagedImage::addGlobalScope('order',function(Builder $builder){
            return $builder->orderBy('sort','ASC');
        });

        $this->loadViewsFrom(__DIR__.'/Views','ImageManager');
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        $this->commands([
            GenerateVariations::class,
        ]);

        $this->publishes([
            __DIR__.'/Vue' => resource_path('js/components1')
        ],'managed-images');
    }

}
