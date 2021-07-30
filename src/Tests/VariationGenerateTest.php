<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\ImageManager;
use Azizyus\ImageManager\DB\Models\ManagedImage;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class VariationGenerateTest extends BaseTestCase
{

    public function testVariations()
    {
        imageManager()->defineVariation('listingPic',300,300,'gallery');
        imageManager()->defineVariation('sliderBig',900,900,'gallery');
        imageManager()->defineVariation('sliderSmall',75,75,'gallery');
        $result = imageManager()->upload($this->fetchUploadedFile());
        imageManager()->maintainVariations($result['fileName']);
        $i = ManagedImage::first();

        $read = file_get_contents($i->getVariation('sliderSmall'));
        $image = Image::make($read);
        $this->assertEquals($image->width(),75);

        $read = file_get_contents($i->getVariation('sliderBig'));
        $image = Image::make($read);
        $this->assertEquals($image->width(),900);

        $read = file_get_contents($i->getVariation('listingPic'));
        $image = Image::make($read);
        $this->assertEquals($image->width(),300);
    }

    public function testVariationsAfterCrop()
    {
        imageManager()->defineVariation('listingPic',300,300,'gallery');
        imageManager()->defineVariation('sliderBig',900,900,'gallery');
        imageManager()->defineVariation('sliderSmall',75,75,'gallery');
        $result = imageManager()->upload($this->fetchUploadedFile());
        $im = \imageManager()->getFileRecord($result['fileName']);
        $oldVariations = $im->variations;

        $cropResult = imageManager()->cropImage($result['fileName'],150,150,500,500);
        $croppedIm = \imageManager()->getFileRecord($cropResult['fileName']);


        array_map(function(string $fileName){
            $this->assertFalse(\imageManager()->checkFileExist($fileName));
        },$oldVariations);

        array_map(function(string $fileName){
            $this->assertTrue(\imageManager()->checkFileExist($fileName));
        },$croppedIm->variations);

    }

}
