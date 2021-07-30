<?php


namespace Azizyus\ImageManager\Tests;


use Illuminate\Http\UploadedFile;

class ImageSortTest extends BaseTestCase
{

    public function testSortUpdate()
    {
        $u = new UploadedFile(__DIR__.'/Images/test_image.jpg','test_image.jpg');
        $result = imageManager()->upload($u);
        $result1 = imageManager()->upload($u);

        //reverse order
        $sortResult = imageManager()->setSort([
            $result1['fileName'],
            $result['fileName'],
        ]);

        $allImages = imageManager()->getFiles();

        //check by 0 -> 1
        //check by 1 -> 0
        $this->assertEquals($allImages[0]['fileName'],$result1['fileName']);
        $this->assertEquals($allImages[1]['fileName'],$result['fileName']);

        $this->assertTrue($sortResult['success']);

    }

}
