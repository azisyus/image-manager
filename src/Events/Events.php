<?php

namespace Azizyus\ImageManager\Events;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\Manager;
use Illuminate\Database\Eloquent\Builder;

class Events
{

    public static function register(callable $f)
    {
        ManagedImage::created(function(ManagedImage $image)use($f){
            $manager = $f();
            $manager->maintainVariations($image->fileName);
        });
        ManagedImage::updated(function(ManagedImage $image)use($f){
            $manager = $f();
            if($image->wasChanged('fileName'))
                $manager->maintainVariations($image->fileName);
        });
        ManagedImage::addGlobalScope('order',function(Builder $builder){
            return $builder->orderBy('sort','ASC');
        });
    }

}
