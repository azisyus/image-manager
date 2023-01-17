<?php

namespace Azizyus\ImageManager\Helper;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\Manager;
use Azizyus\ImageManager\Naming\Generators;
use Illuminate\Support\Facades\Storage;

class ExampleSingletob
{

    public static function sin()
    {
        //storage drive of image
        ManagedImage::setStorageDriver(Storage::disk('public'));


        //storage image for manager
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
            $s->defineSpecialImageWithArrayOptions('thumbnail',[
                'width' => 150,
                'height' =>  150,
                'cropAspectRestricted' => true,
                'targetExtension' => 'webp',
            ]);

            $s->defineSpecialImageWithArrayOptions('cover',[
                'width' => 150,
                'height' =>  150,
                'cropAspectRestricted' => true,
                'targetExtension' => 'webp',
            ]);

            $s->defineVariationImageWithOptions('sliderListingImage',[
                'width' => 75,
                'height' =>  75,
                'type' => 'gallery',
                'targetExtension' => 'webp',
            ]);

            $s->defineVariationImageWithOptions('zoneThumbnail',[
                'type' => 'zoneThumbnail',
                'width' => 150,
                'height' => 150,
                'targetExtension' => 'webp',
            ]);

            $s->setUploadUrl(route('image.upload'));
            $s->setFilesUrl(route('image.files'));

            $s->setNameGenerator(Generators::unique());

            return $s;
        });
    }


    public static function fin()
    {
        ManagedImage::setStorageDriver(Storage::disk('public'));
        app()->singleton('imageManagerSingular',function(){
            $s = new Manager(Storage::disk('public'));
            $s->setDeleteUrl(route('singular.image.delete'));
            $s->setUploadUrl(route('singular.image.upload'));
            $s->setFilesUrl(route('singular.image.files'));
            $s->setCropFilesUrl(route('singular.image.crop'));

            $s->setUploadUrl(route('singular.image.upload'));
            $s->setFilesUrl(route('singular.image.files'));

            $s->setSortUrl(route('singular.image.sort'));
            $s->setRemoteUrlUploadUrl(route('singular.image.remote'));
            $s->setSpecialImagesUrl(route('singular.image.specialImages'));
            $s->setChooseSpecialImageUrl(route('singular.image.chooseSpecialImage'));

            $s->setUploadImageLimit(1);

            $s->setNameGenerator(Generators::forced('webp'));

            return $s;
        });
    }



}
