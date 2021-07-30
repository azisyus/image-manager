<?php


namespace Azizyus\ImageManager\Tests;


use Intervention\Image\Facades\Image;

class SpecialImageTest extends BaseTestCase
{

    public function testSpecialImageGeneration()
    {

        imageManager()->defineSpecialImage('listingThumbnail',150,150);
        $result = imageManager()->upload($this->fetchUploadedFile());

        imageManager()->chooseSpecialImage('listingThumbnail',$result['fileName']);

        $listingThumbnail = imageManager()->getImageByType('listingThumbnail');

        $im = Image::make(file_get_contents($listingThumbnail['variations']['listingThumbnail']));
        $this->assertEquals($im->height(),150);
        $this->assertEquals($im->width(),150);

    }

}
