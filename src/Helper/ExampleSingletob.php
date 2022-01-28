<?php

namespace Azizyus\ImageManager\Helper;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\Manager;
use Illuminate\Support\Facades\Storage;

class ExampleSingletob
{

    public static function sin()
    {
        ManagedImage::setStorageDriver(Storage::disk('public'));
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
            ]);

            $s->defineSpecialImageWithArrayOptions('cover',[
                'width' => 150,
                'height' =>  150,
                'cropAspectRestricted' => true,
            ]);

            $s->defineVariationImageWithOptions('sliderListingImage',[
                'width' => 75,
                'height' =>  75,
                'type' => 'gallery',
            ]);

            $s->setUploadUrl(route('image.upload'));
            $s->setFilesUrl(route('image.files'));
            return $s;
        });
    }

}
