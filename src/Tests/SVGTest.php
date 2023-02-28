<?php

namespace Azizyus\ImageManager\Tests;

use Azizyus\ImageManager\ImageManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SVGTest extends BaseTestCase
{

    public function testWithSVGFile()
    {
        $this->withoutExceptionHandling();
        $file = $this->fetchSVGFile();
        $r = Request::create('_','POST',[],[],[
            'file' => $file,
        ]);
        $result = ImageManager::upload($r,$this->manager());
        $this->assertEquals(false,$result['cropable']);
        $this->assertEquals('svg',explode('.',$result['fileName'])[1]);
        $this->assertEquals('image/svg+xml',File::mimeType(public_path('storage/').$result['fileName']).'+xml');
    }


}
