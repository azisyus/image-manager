<?php


namespace Azizyus\ImageManager\DB;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\Exceptions\FileRecordDoesntExist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Repository
{

    /**
     * @var Model $model
     */
    protected $model = null;

    public function setModel(Model $model = null)
    {
        $this->model = $model;
    }

    /**
     * @return int
     */
    public function getModelImageCount() : int
    {
        return $this->baseQuery()
            ->where('type','gallery')
            ->count();
    }

    protected function baseQuery()
    {
        if($this->model)
            return ManagedImage::query()
                ->where('relatedModelId',$this->model->getKey())
                ->where('relatedModel',get_class($this->model));

        return ManagedImage::query();
    }

    public function getByTypes(array $type = null)
    {
        return $this->baseQuery()->whereIn('type',$type)->get();
    }

    public function getByType(string $type = null) : ?ManagedImage
    {
        return $this->baseQuery()->where('type',$type)->first();
    }

    public function createImage(string $fileName,string $originalFileName, int $size,string $extension,string $type=null) : ManagedImage
    {
        return $this->baseQuery()->create([
            'fileName' => $fileName,
            'originalFileName' => $originalFileName,
            'size' => $size,
            'extension' => $extension,
            'sort' => $this->baseQuery()->count(),
            'relatedModelId' => $this->model!==null ? $this->model->getKey() : null,
            'relatedModel' => $this->model!==null ? get_class($this->model) : null,
            'type' => $type
        ]);
    }

    public function all()
    {
        return $this->getByTypes(['gallery']);
    }

    public function deleteFileByName(string $fileName)
    {
        $f = $this->baseQuery()
            ->where('fileName',$fileName)
            ->first();
        if($f)
            return $f->delete();
        return false;
    }

    public function setVariations(string $fileName,array $variations) : void
    {
        $found = $this->getByFileName($fileName);

        if(!$found)
            throw new FileRecordDoesntExist();

        $found->variations = $variations;
        $found->save();
    }

    public function getByFileName(string $fileName)
    {
        return $this->baseQuery()->where('fileName',$fileName)->first();
    }

    public function updateSort(string $fileName,int $sort)
    {
        $this->baseQuery()->where('fileName',$fileName)->update([
            'sort'=>$sort,
        ]);
    }

    public function replaceName(string $oldName,string $newName)
    {
        $this->baseQuery()->where('fileName',$oldName)->update([
            'fileName'=>$newName,
        ]);
    }

    public function withIds(array $ids = []) : Builder
    {
        return $this->baseQuery()->whereIn('id',$ids);
    }

}
