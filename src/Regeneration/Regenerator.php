<?php

namespace Azizyus\ImageManager\Regeneration;

use Azizyus\ImageManager\DB\Models\ManagedImage;

class Regenerator
{

    public static function generate(\Azizyus\ImageManager\Manager $imageManager)
    {
        $total = 0;
        for ($i=0;$i>=0;$i++)
        {
            $images = ManagedImage::skip(20*$i)->take(20)->get();
            $total+= $images->count();
            if($images->count())
                foreach ($images->all() as $item)
                {
                    $imageManager->maintainVariations($item->fileName);
                    echo ('generated file name: '.$item->fileName .PHP_EOL);
                }
            else
            {
                echo ('end of the images; '.$total.' image processed'.PHP_EOL);
                break;
            }
        }
        echo('Completed variation generation'.PHP_EOL);
        return 0;
    }

}
