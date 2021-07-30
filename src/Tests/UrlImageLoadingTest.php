<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\ImageManager;
use Faker\Provider\Image;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UrlImageLoadingTest extends BaseTestCase
{

    public function testUrlImageUploading()
    {

        $u = new UploadedFile(__DIR__.'/Images/test_image.jpg',"UL_IMAGE",mime_content_type(__DIR__.'/Images/test_image.jpg'),UPLOAD_ERR_OK,true);

        $request = new Request([
            'file' => $u
        ]);

        $val = Validator::make($request->all(),[
            'file' => \imageManager()->getValidation(),
        ]);
        if($val->fails())
            $this->assertTrue(false);

        $result = imageManager()->upload($u);



        $result1 = imageManager()->importFromUrl($result['imgSrc']);
        $this->assertEquals(true,$result1['success']);

        $this->assertEquals(2,\imageManager()->getFiles()->count());

    }

}
