<?php


namespace Azizyus\ImageManager\Tests;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\ImageManager;

class QueryTest extends BaseTestCase
{

    public function testWrapping()
    {

        $this->manager()->upload($this->fetchUploadedFile());
        $this->manager()->upload($this->fetchUploadedFile());
        $this->manager()->upload($this->fetchUploadedFile());

        $lastImage =  ManagedImage::orderBy('id','DESC')->first();
        ImageManager::withModel($lastImage,$this->manager(),function(){
            $this->manager()->upload($this->fetchUploadedFile());
            $this->manager()->upload($this->fetchUploadedFile());
            $this->manager()->upload($this->fetchUploadedFile());
        });

        $relatedImageCount = ImageManager::withModel($lastImage,$this->manager(),function(){
            return $this->manager()->getFiles();
        })->count();

        $allImagesCount = $this->manager()->getFiles()->count();

        $this->assertEquals(3,$relatedImageCount);
        $this->assertEquals(6,$allImagesCount);
    }

    public function testOrder()
    {
        $c = collect([0,1,2]);

        $fileNames = array_map(function($x){
            return $x['fileName'];
        },[
            $this->manager()->upload($this->fetchUploadedFile()),
            $this->manager()->upload($this->fetchUploadedFile()),
            $this->manager()->upload($this->fetchUploadedFile()),
        ]);

        $this->manager()->setSort(array_reverse($fileNames));

        $queriedFileNames = array_map(function($x){
            return $x['fileName'];
        },$this->manager()->getFiles()->toArray());

        //reversed of reversed should be equal to first values
        $this->assertEquals(array_reverse($queriedFileNames),$fileNames);

    }

}
