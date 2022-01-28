<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\DB\Test\NextTestModel;
use Azizyus\ImageManager\DB\Test\TestModel;
use Azizyus\ImageManager\ImageManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Facades\Image;

class CopyTest extends BaseTestCase
{

    private function defineTestHead()
    {
        foreach (['test_models','next_test_models'] as $t)
        {
            Schema::create($t,function(Blueprint $blueprint){
                $blueprint->id();
                $blueprint->string('name')->nullable();
                $blueprint->timestamps();
            });
        }
        TestModel::create();

        NextTestModel::create();
        NextTestModel::create();
        $this->manager()->defineVariation('listingPic',300,300,'gallery');
        $this->manager()->defineVariation('sliderBig',900,900,'gallery');
        $this->manager()->defineVariation('sliderSmall',75,75,'gallery');


    }

    public function testCopy()
    {

        $this->defineTestHead();
        $t1 = TestModel::first();
        $t2 = NextTestModel::orderBy('id','DESC')->first();
        $result = ImageManager::withModel($t1,$this->manager(),function(){
            $result = $this->manager()->upload($this->fetchUploadedFile());
            $this->manager()->upload($this->fetchUploadedFile());
            $this->manager()->upload($this->fetchUploadedFile());
            $this->manager()->chooseSpecialImage('listingPic',$result['fileName']);
            $this->manager()->chooseSpecialImage('listingPic',$result['fileName']);
            $this->manager()->chooseSpecialImage('sliderSmall',$result['fileName']);
            return $result;
        });

        $this->assertTrue($result['success']);
        $this->manager()->copyImageIntoNewModel($t1,$t2);

        $this->assertEquals($t1->wholeImages()->count(),$t2->wholeImages()->count());
        $t2Images = $t2->wholeImages()->get();
        /**
         * @var ManagedImage $im
         * @var ManagedImage $t2Image
         */
        foreach ($t1->wholeImages()->get() as $key => $im)
        {
            $t2Image = $t2Images[$key];
            $this->assertNotEquals($im->originalFileName,$t2Image->originalFileName);
            $this->assertNotEquals($im->fileName,$t2Image->fileName);
            $this->assertNotEquals($im->relatedModel,$t2Image->relatedModel);
            $this->assertNotEquals($im->relatedModelId,$t2Image->relatedModelId);
            $this->assertNotEquals($im->attributesToArray(),$t2Image->attributesToArray());
        }



    }


    public function testDirectCopyImage()
    {

        $this->defineTestHead();
        $t1 = TestModel::first();
        $result = ImageManager::withModel($t1,$this->manager(),function(){
            $result = $this->manager()->upload($this->fetchUploadedFile());
            $this->manager()->upload($this->fetchUploadedFile());
            $this->manager()->upload($this->fetchUploadedFile());
            $this->manager()->chooseSpecialImage('listingPic',$result['fileName']);
            $this->manager()->chooseSpecialImage('listingPic',$result['fileName']);
            $this->manager()->chooseSpecialImage('sliderSmall',$result['fileName']);
            return $result;
        });
        $t1->refresh();

        $t1->load('listingPicImage');

        $copier = $this->manager()->copyImage();
        $newImageName = $copier($t1->listingPicImage->variations['zoneThumbnail']);


        $this->assertTrue($this->manager()->checkFileExist($newImageName));
        $image = Image::make($this->manager()->generateFileUrl($newImageName));
        $this->assertEquals(150,$image->height());
        $this->assertEquals(150,$image->width());
    }

}
