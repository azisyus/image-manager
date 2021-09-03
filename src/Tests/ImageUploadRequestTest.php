<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\Requests\UploadFileRequest;
use Illuminate\Validation\ValidationException;

class ImageUploadRequestTest extends BaseTestCase
{

    public function testWithTextFile()
    {
        $this->expectException(ValidationException::class);
        $file = $this->fetchTxtFile();
        $r = new UploadFileRequest();
        $r->merge([
            'file' => $file,
        ]);
        $r->validate($r->rules());
    }

    public function testWithImageFile()
    {
        $this->withoutExceptionHandling();
        $file = $this->fetchUploadedFile();
        $r = new UploadFileRequest();
        $r->merge([
            'file' => $file,
        ]);
        $r->validate($r->rules());
        $this->assertTrue(true);
    }


}
