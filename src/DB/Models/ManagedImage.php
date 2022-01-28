<?php


namespace Azizyus\ImageManager\DB\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;

/**
 * Class ManagedImage
 * @package Azizyus\ImageManager\DB\Models
 *
 * @property string $fileName
 * @property string $originalFileName
 * @property int $size
 * @property string $extension
 * @property array $variations
 * @property int $sort
 * @property string $type
 * @property string $relatedModelId
 * @property string $relatedModel
 *
 *
 *
 */

class ManagedImage extends Model
{
    protected $fillable = [
        'fileName',
        'originalFileName',
        'size',
        'extension',
        'variations' ,
        'sort',
        'type',
        'relatedModelId',
        'relatedModel',
    ];

    protected $attributes = [
        'variations' => '[]'
    ];

    protected $casts = [
        'variations' => 'array'
    ];

    protected static $storage;

    public static function setStorageDriver(FilesystemAdapter $f)
    {
        static::$storage = $f;
    }

    public static function getStorageDriver()
    {
        return static::$storage;
    }

    public function getVariation(string $variation) : ?string
    {
        $found = Arr::get($this->variations,$variation,null);
        if($found)
            return static::$storage->url($found);
        return null;
    }

    public static function mapper()
    {
        return function (ManagedImage $image){

            $filesystem = static::$storage;
            return [
                'variations' => array_map(function($item)use($filesystem){
                    return $filesystem->url($item);
                },$image->variations),
                'fileName' => $image->fileName,
                'imgSrc' => $filesystem->url($image->fileName),
                'originalSrc' => $filesystem->url($image->originalFileName),
            ];
        };
    }

    public function map()
    {
        $f = static::mapper();
        return $f($this);
    }

    public function toArray()
    {
        return $this->map();
    }

}
