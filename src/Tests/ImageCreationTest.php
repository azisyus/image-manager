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

        $u = $this->fetchUploadedFile();
        $result = $this->manager()->upload($u);
        $src = $result['imgSrc'];
        $originalSrc = $result['originalSrc'];
        $this->assertTrue($result['success']);
        $file = file_get_contents($src);

        //check uploaded and original file has same size,
        $this->assertEquals($u->getSize(),strlen($file));


        //check croppable status
        $this->assertEquals(true,$result['cropable']);


        //deleted file will return 404
        $this->manager()->deleteFile($result['fileName']);
        $result = $this->get($src);
        $result->assertStatus(404);

        //deleted file will return 404
        $result = $this->get($originalSrc);
        $result->assertStatus(404);

    }

    public function testFetchSpecificImages()
    {

        $u = $this->fetchUploadedFile();
        $this->manager()->upload($u);
        $this->manager()->upload($u);
        $this->manager()->upload($u);
        $this->manager()->upload($u);
        $this->manager()->upload($u);


        $ids = [1,2,4];
        $result = $this->manager()->withIds($ids);
        $this->assertEquals(3,count($result));
    }

    public function testUploadImageLimit()
    {
        $this->withoutExceptionHandling();
        ImageManager::setUploadLimit(3,$this->manager());
        $r = Request::create('_','POST',[],[],['file' => $this->fetchUploadedFile()]);
        ImageManager::upload($r,$this->manager());
        ImageManager::upload($r,$this->manager());
        ImageManager::upload($r,$this->manager());

        $this->expectException(ValidationException::class);
        ImageManager::upload($r,$this->manager());

        $this->assertEquals(3,ImageManager::getModelImageCount($this->manager()));


    }


}
