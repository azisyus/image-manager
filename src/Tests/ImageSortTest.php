<?php


namespace Azizyus\ImageManager\Tests;


use Illuminate\Http\UploadedFile;

class ImageSortTest extends BaseTestCase
{

    public function testSortUpdate()
    {
        $u = $this->fetchUploadedFile();
        $result = $this->manager()->upload($u);
        $result1 = $this->manager()->upload($u);

        //reverse order
        $sortResult = $this->manager()->setSort([
            $result1['fileName'],
            $result['fileName'],
        ]);

        $allImages = $this->manager()->getFiles();

        //check by 0 -> 1
        //check by 1 -> 0
        $this->assertEquals($allImages[0]['fileName'],$result1['fileName']);
        $this->assertEquals($allImages[1]['fileName'],$result['fileName']);

        $this->assertTrue($sortResult['success']);

    }

}
