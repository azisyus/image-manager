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

    public function testSpecialImageGenerationWithNoCanvas()
    {

        imageManager()->defineSpecialImage('listingThumbnail',150,150);
        imageManager()->defineVariationImageWithOptions('thumbnail',[

            'type'   => 'listingThumbnail',
            'width'  => 90,
            'height' =>  90,
            'noCanvas' => true,

        ]);
        $result = imageManager()->upload($this->fetch1dot8MbFile());
        imageManager()->chooseSpecialImage('listingThumbnail',$result['fileName']);

        $listingThumbnail = imageManager()->getImageByType('listingThumbnail');

        $im = Image::make(file_get_contents($listingThumbnail['variations']['listingThumbnail']));
        $this->assertEquals($im->height(),150);
        $this->assertEquals($im->width(),150);


        $im = Image::make(file_get_contents($listingThumbnail['variations']['thumbnail']));
        $this->assertEquals($im->height(),47);
        $this->assertEquals($im->width(),90);

    }


}
