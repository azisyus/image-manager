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
        $this->manager()->defineVariation('listingPic',300,300,'gallery');
        $this->manager()->defineVariation('sliderBig',900,900,'gallery');
//        $this->manager()->defineVariation('sliderSmall',75,75,'gallery');
        $this->manager()->defineVariationImageWithOptions('sliderSmall',[
            'width' => 75,
            'height' => 75,
            'targetExtension' => 'webp',
        ],'gallery');
        $result = $this->manager()->upload($this->fetchUploadedFile());
        $this->manager()->maintainVariations($result['fileName']);
        $i = ManagedImage::first();

        $read = file_get_contents($i->getVariation('sliderSmall'));
        $image = Image::make($read);
        $this->assertEquals($image->width(),75);
        $this->assertEquals('image/webp',$image->mime); //this is the only variation with forced

        $read = file_get_contents($i->getVariation('sliderBig'));
        $image = Image::make($read);
        $this->assertEquals($image->width(),900);
        $this->assertEquals('image/jpeg',$image->mime);

        $read = file_get_contents($i->getVariation('listingPic'));
        $image = Image::make($read);
        $this->assertEquals($image->width(),300);
        $this->assertEquals('image/jpeg',$image->mime);

        //forced extensions does not effect original image possibly you might want to re-download and somehow share the original file with user
        $read = file_get_contents($result['originalSrc']);
        $image = Image::make($read);
        $this->assertEquals($image->width(),400);
        $this->assertEquals('image/jpeg',$image->mime);
    }

    public function testVariationsAfterCrop()
    {
        $this->manager()->defineVariation('listingPic',300,300,'gallery');
        $this->manager()->defineVariation('sliderBig',900,900,'gallery');
        $this->manager()->defineVariation('sliderSmall',75,75,'gallery');
        $result = $this->manager()->upload($this->fetchUploadedFile());
        $im = $this->manager()->getFileRecord($result['fileName']);
        $oldVariations = $im->variations;

        $cropResult = $this->manager()->cropImage($result['fileName'],150,150,500,500);
        $croppedIm = $this->manager()->getFileRecord($cropResult['fileName']);


        array_map(function(string $fileName){
            $this->assertFalse($this->manager()->checkFileExist($fileName));
        },$oldVariations);

        array_map(function(string $fileName){
            $this->assertTrue($this->manager()->checkFileExist($fileName));
        },$croppedIm->variations);

    }

}
