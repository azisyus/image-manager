<?php


namespace Azizyus\ImageManager\Tests;


use Intervention\Image\Facades\Image;

class SpecialImageTest extends BaseTestCase
{

    public function testSpecialImageGeneration()
    {

        $this->manager()->defineSpecialImage('listingThumbnail',150,150);
        $result = $this->manager()->upload($this->fetchUploadedFile());

        $this->manager()->chooseSpecialImage('listingThumbnail',$result['fileName']);

        $listingThumbnail = $this->manager()->getImageByType('listingThumbnail');

        $im = Image::make(file_get_contents($listingThumbnail['variations']['listingThumbnail']));
        $this->assertEquals($im->height(),150);
        $this->assertEquals($im->width(),150);

    }

    public function testSpecialImageGenerationWithNoCanvas()
    {

        $this->manager()->defineSpecialImage('listingThumbnail',150,150);
        $this->manager()->defineVariationImageWithOptions('thumbnail',[

            'type'   => 'listingThumbnail',
            'width'  => 90,
            'height' =>  90,
            'noCanvas' => true,

        ]);
        $this->manager()->setValidation('max:2084|mimes:jpg');
        $result = $this->manager()->upload($this->fetch1dot8MbFile());
        $this->manager()->chooseSpecialImage('listingThumbnail',$result['fileName']);

        $listingThumbnail = $this->manager()->getImageByType('listingThumbnail');

        $im = Image::make(file_get_contents($listingThumbnail['variations']['listingThumbnail']));
        $this->assertEquals($im->height(),150);
        $this->assertEquals($im->width(),150);


        $im = Image::make(file_get_contents($listingThumbnail['variations']['thumbnail']));
        $this->assertEquals($im->height(),47);
        $this->assertEquals($im->width(),90);

    }


}
