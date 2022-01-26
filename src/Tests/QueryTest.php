<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\ImageManager;

class QueryTest extends BaseTestCase
{

    public function testWrapping()
    {

        \imageManager()->upload($this->fetchUploadedFile());
        \imageManager()->upload($this->fetchUploadedFile());
        \imageManager()->upload($this->fetchUploadedFile());

        $lastImage =  ManagedImage::orderBy('id','DESC')->first();
        ImageManager::withModel($lastImage,\imageManager(),function(){
            \imageManager()->upload($this->fetchUploadedFile());
            \imageManager()->upload($this->fetchUploadedFile());
            \imageManager()->upload($this->fetchUploadedFile());
        });

        $relatedImageCount = ImageManager::withModel($lastImage,\imageManager(),function(){
            return ImageManager()->getFiles();
        })->count();

        $allImagesCount = ImageManager()->getFiles()->count();

        $this->assertEquals(3,$relatedImageCount);
        $this->assertEquals(6,$allImagesCount);
    }

    public function testOrder()
    {
        $c = collect([0,1,2]);

        $fileNames = array_map(function($x){
            return $x['fileName'];
        },[
            imageManager()->upload($this->fetchUploadedFile()),
            imageManager()->upload($this->fetchUploadedFile()),
            imageManager()->upload($this->fetchUploadedFile()),
        ]);

        \imageManager()->setSort(array_reverse($fileNames));

        $queriedFileNames = array_map(function($x){
            return $x['fileName'];
        },\imageManager()->getFiles()->toArray());

        //reversed of reversed should be equal to first values
        $this->assertEquals(array_reverse($queriedFileNames),$fileNames);

    }

}
