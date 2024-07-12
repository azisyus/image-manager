<?php


namespace Azizyus\ImageManager\DB\Models;


use Illuminate\Database\Eloquent\Builder;
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
 * @property string $groupName
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
        'groupName',
        'alt'
    ];

    protected $attributes = [
        'variations' => '[]',
        'alt' => '{}'
    ];

    protected $casts = [
        'variations' => 'array',
        'alt' => 'object'
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

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order',function(Builder $builder){
            return $builder->orderBy('sort','ASC');
        });
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
                'alt' => $image->alt
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

    public static function specificGroup(callable $built,$group = null) : ?object
    {
        static::addGlobalScope('group',function($query)use($group){
            $query->where('groupName',$group);
        });
        $result = $built();
        static::addGlobalScope('group',function($query)use($group){});
        return $result;
    }


}
