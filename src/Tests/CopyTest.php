<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\DB\Test\NextTestModel;
use Azizyus\ImageManager\DB\Test\TestModel;
use Azizyus\ImageManager\ImageManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CopyTest extends BaseTestCase
{

    public function testCopy()
    {
        foreach (['test_models','next_test_models'] as $t)
        {
            Schema::create($t,function(Blueprint $blueprint){
                $blueprint->id();
                $blueprint->string('name')->nullable();
                $blueprint->timestamps();
            });
        }
        $t1 = TestModel::create();

        NextTestModel::create();
        $t2 = NextTestModel::create();
        imageManager()->defineVariation('listingPic',300,300,'gallery');
        imageManager()->defineVariation('sliderBig',900,900,'gallery');
        imageManager()->defineVariation('sliderSmall',75,75,'gallery');

        $result = ImageManager::withModel($t1,function(){
            $result = imageManager()->upload($this->fetchUploadedFile());
            imageManager()->upload($this->fetchUploadedFile());
            imageManager()->upload($this->fetchUploadedFile());
            \imageManager()->chooseSpecialImage('listingPic',$result['fileName']);
            \imageManager()->chooseSpecialImage('listingPic',$result['fileName']);
            \imageManager()->chooseSpecialImage('sliderSmall',$result['fileName']);
            return $result;
        });

        $this->assertTrue($result['success']);
        imageManager()->copyImageIntoNewModel($t1,$t2);

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

}
