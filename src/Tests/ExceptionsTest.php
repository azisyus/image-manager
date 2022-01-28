<?php


namespace Azizyus\ImageManager\Tests;


use Illuminate\Validation\ValidationException;

class ExceptionsTest extends BaseTestCase
{

    public function testGoodUrl()
    {
        $result = $this->manager()->upload($this->fetchUploadedFile());
        $importResult1 = $this->manager()->importFromUrl($result['imgSrc']);
        $this->assertTrue($importResult1['success']);
    }

    public function testBadUrl()
    {
        $this->expectException(ValidationException::class);
        $this->manager()->importFromUrl('http://localhost/file.jpg');
    }

}
