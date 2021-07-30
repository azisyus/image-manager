<?php


namespace Azizyus\ImageManager\Tests;


use Intervention\Image\Facades\Image;

class ExceptionsTest extends BaseTestCase
{

    public function testBadUrl()
    {
        $result = imageManager()->upload($this->fetchUploadedFile());
        $importResult = imageManager()->importFromUrl('https://localhost/file');
        $importResult1 = imageManager()->importFromUrl($result['imgSrc']);

        $this->assertFalse($importResult['success']);
        $this->assertTrue($importResult1['success']);
    }


}
