<?php

namespace Azizyus\ImageManager\Helper;


use Azizyus\ImageManager\Manager;
use Illuminate\Support\Facades\Storage;

class ExampleSingletob
{

    public static function sin()
    {
        app()->singleton('imageManager',function(){
            $s = new Manager(Storage::disk('public'));
            $s->setDeleteUrl('/delete');
            $s->setUploadUrl('/upload');
            $s->setFilesUrl('/files');
            $s->setSortUrl('/sort');
            $s->setCropFilesUrl('/crop');
            $s->setRemoteUrlUploadUrl('/remote');
            $s->setSpecialImagesUrl('/specialImages');
            $s->setChooseSpecialImageUrl('/chooseSpecialImage');
            $s->defineSpecialImage('thumbnail',150,150); //choose thumbnail from uploaded images
            $s->defineSpecialImage('cover',150,150); //choose thumbnail from uploaded images
            $s->defineVariation('sliderListingImage',75,75,'gallery'); //generate variation for uploaded images except special ones
            $s->setUploadUrl(route('image.upload'));
            $s->setFilesUrl(route('image.files'));
            return $s;
        });
    }

}
