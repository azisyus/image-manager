<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\Helper\ExampleSingletob;
use Illuminate\Http\UploadedFile;
use Tests\CreatesApplication;
use Tests\TestCase;

class BaseTestCase extends TestCase
{

    use CreatesApplication;

    protected function manager()
    {
        return app('imageManager');
    }

    protected function singularManager()
    {
        return app('imageManagerSingular');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    protected function fetchUploadedFile() : UploadedFile
    {
        return new UploadedFile(__DIR__.'/Images/test_image.jpg',"UL_IMAGE",mime_content_type(__DIR__.'/Images/test_image.jpg'),UPLOAD_ERR_OK,true);
    }

    protected function fetchTxtFile() : UploadedFile
    {
        return new UploadedFile(__DIR__.'/File/test.txt',"UL_IMAGE",mime_content_type(__DIR__.'/File/test.txt'),UPLOAD_ERR_OK,true);
    }

    protected function fetch1dot8MbFile() : UploadedFile
    {
        return new UploadedFile(__DIR__.'/Images/1.8mb_image.jpg',"UL_IMAGE",mime_content_type(__DIR__.'/Images/1.8mb_image.jpg'),UPLOAD_ERR_OK,true);
    }

}
