<?php


namespace Azizyus\ImageManager\Tests;


use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class ExceptionsTest extends BaseTestCase
{

    public function testGoodUrl()
    {
        $result = imageManager()->upload($this->fetchUploadedFile());
        $importResult1 = imageManager()->importFromUrl($result['imgSrc']);
        $this->assertTrue($importResult1['success']);
    }

    public function testBadUrl()
    {
        $this->expectException(ValidationException::class);
        imageManager()->importFromUrl('http://localhost/file.jpg');
    }

}
