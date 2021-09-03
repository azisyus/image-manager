<?php


namespace Azizyus\ImageManager\Tests;


use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BaseTestCase extends TestCase
{

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

}
