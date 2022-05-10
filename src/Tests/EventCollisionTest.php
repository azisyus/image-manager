<?php

namespace Azizyus\ImageManager\Tests;

use Illuminate\Support\Arr;
use Intervention\Image\Facades\Image;

class EventCollisionTest extends BaseTestCase
{

    public function testEventCollision()
    {
        $this->manager()->setValidation('mimes:jpeg,jpg,png,bmp|max:2048');
        $this->manager()->defineVariation('zoneThumbnail',10,10,'zoneThumbnail');


        //should not be effected by other instance
        $result = $this->singularManager()->upload($this->fetchUploadedFile());
        $imageSingular = Image::make(file_get_contents(Arr::get($result,'variations.zoneThumbnail')));

        $this->assertEquals(150,$imageSingular->width());
        $this->assertEquals(150,$imageSingular->height());
    }

}
