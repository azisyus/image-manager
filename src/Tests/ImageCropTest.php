<?php


namespace Azizyus\ImageManager\Tests;


use Intervention\Image\Facades\Image;

class ImageCropTest extends BaseTestCase
{


    public function testCropImage()
    {
        $result = $this->manager()->upload($this->fetchUploadedFile());
        $result = $this->manager()->cropImage($result['fileName'],2,2,2,2);

        $image = Image::make(file_get_contents($result['imgSrc']));

        $this->assertEquals($image->width(),2);
        $this->assertEquals($image->height(),2);
    }

    public function testCropped()
    {

        $result = $this->manager()->upload($this->fetchUploadedFile());
        $resultCropped = $this->manager()->cropImage($result['fileName'],97,87,178,74);

        $file  = file_get_contents($resultCropped['imgSrc']);
        $file1 = file_get_contents(__DIR__.'/Images/60feee7a9ea1f.jpg');
        $this->assertEquals($file,$file1);
    }

}
