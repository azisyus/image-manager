<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\ImageManager;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImageCreationTest extends BaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function testCreation()
    {

        $u = new UploadedFile(__DIR__.'/Images/test_image.jpg','test_image.jpg');
        $result = imageManager()->upload($u);
        $src = $result['imgSrc'];
        $originalSrc = $result['originalSrc'];
        $this->assertTrue($result['success']);
        $file = file_get_contents($src);

        //check uploaded and original file has same size,
        $this->assertEquals($u->getSize(),strlen($file));



        //deleted file will return 404
        imageManager()->deleteFile($result['fileName']);
        $result = $this->get($src);
        $result->assertStatus(404);

        //deleted file will return 404
        $result = $this->get($originalSrc);
        $result->assertStatus(404);

    }

    public function testFetchSpecificImages()
    {

        $u = new UploadedFile(__DIR__.'/Images/test_image.jpg','test_image.jpg');
        imageManager()->upload($u);
        imageManager()->upload($u);
        imageManager()->upload($u);
        imageManager()->upload($u);
        imageManager()->upload($u);


        $ids = [1,2,4];
        $result = imageManager()->withIds($ids);
        $this->assertEquals(3,count($result));
    }

    public function testUploadImageLimit()
    {
        $this->withoutExceptionHandling();
        ImageManager::setUploadLimit(3);
        $r = Request::create('_','POST',[],[],['file' => $this->fetchUploadedFile()]);
        ImageManager::upload($r);
        ImageManager::upload($r);
        ImageManager::upload($r);

        $this->expectException(ValidationException::class);
        ImageManager::upload($r);

        $this->assertEquals(3,ImageManager::getModelImageCount());


    }


}
