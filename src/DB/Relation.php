<?php


namespace Azizyus\ImageManager\DB;


use Azizyus\ImageManager\DB\Models\ManagedImage;

trait Relation
{

    /**
     *
     * @method $allImages
     *
     */

    public function __call($name, $arguments)
    {
        if(str_ends_with($name,'Image'))
        {
            return $this->hasOne(ManagedImage::class,'relatedModelId','id')
                ->where('relatedModel',$this->getMorphClass())
                ->where('type',str_replace('Image','',$name));
        }
        else if($name === 'allImages')
        {
            return $this->hasMany(ManagedImage::class,'relatedModelId','id')
                ->where('relatedModel',$this->getMorphClass())
                ->where('type','gallery');
        }
        else if($name === 'wholeImages')
        {
            //no type will be provided to fetch everything
            //it's mostly used while copying models
            //there is way to guess dynamically defined image relations so simply we fetch everything
            return $this->hasMany(ManagedImage::class,'relatedModelId','id')
                ->where('relatedModel',$this->getMorphClass());
        }
        return parent::__call($name,$arguments);
    }

}
