<?php


namespace Azizyus\ImageManager\DB\Models;


use Illuminate\Database\Eloquent\Model;
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

    public function getVariation(string $variation) : ?string
    {
        $found = Arr::get($this->variations,$variation,null);
        if($found)
            return imageManager()->generateFileUrl($found);
        return null;
    }

    public function map()
    {
        return imageManager()->map()($this);
    }

}
