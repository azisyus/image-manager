<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\ImageManager;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class ImageUploadValidationTest extends BaseTestCase
{

    public function testMaxFileSize()
    {
        $this->expectException(ValidationException::class); //validation error due to bigger file size than expected
        $this->withoutExceptionHandling();
        $f = $this->fetch1dot8MbFile();
        $r = Request::create('_','POST',[],[],[
            'file' => $f,
        ]);
        ImageManager::upload($r);
    }

    public function testMaxFileSizeWithDifferentValidation()
    {
        $this->withoutExceptionHandling();
        $f = $this->fetch1dot8MbFile();
        $r = Request::create('_','POST',[],[],[
            'file' => $f,
        ]);
        ImageManager::setValidation('max:2048');
        ImageManager::upload($r);
        $this->assertTrue(true);
    }

}
