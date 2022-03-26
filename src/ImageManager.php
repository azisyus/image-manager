<?php


namespace Azizyus\ImageManager;


use Azizyus\ImageManager\Helper\AspectRatioChecker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use function response;

class ImageManager
{


    public static function putModel(Model $model = null,Manager $manager)
    {
        $manager->getRepository()->setModel($model);
    }

    public static function withModel(Model $model,Manager $manager,callable $w)
    {
        $manager->getRepository()->setModel($model);
        $result = $w();
        $manager->getRepository()->setModel(null);
        return $result;
    }

    public static function chooseSpecialImage(Request $request,Manager $manager)
    {
        $type = $request->get('type');
        $fileName = $request->get('fileName');
        $r = $manager->chooseSpecialImage($type,$fileName);
        if($r['success'])
            return $r;
        return response($r,400);
    }

    public static function setValidation(string $s,Manager $manager)
    {
        $manager->setValidation($s);
    }

    public static function specialImages(Manager $manager)
    {
        $r = $manager->specialImages();
        if($r['success'])
            return $r;
        return response($r,400);
    }

    public static function importFromUrl(Request $request,Manager $manager)
    {
        $url = $request->get('url');
        $result = $manager->importFromUrl($url);

        if($result['success'])
            return $result; // simply it's 200
        return response($result,400);
    }

    public static function setSort(Request $request,Manager $manager)
    {
        $filesNames = $request->get('fileNames',[]);
        $result = $manager->setSort($filesNames);
        if($result['success'])
            return $result;
        return response($result,400);
    }

    public static function setUploadLimit(int $limit,Manager $manager)
    {
        $manager->setUploadImageLimit($limit);
    }

    public static function getModelImageCount(Manager $manager) : int
    {
        return $manager->getRepository()->getModelImageCount();
    }

    public static function upload(Request $request,Manager $manager)
    {
        $file = $request->file('file');
        $result = $manager->upload($file);

        if($result['success'])
            return $result; // simply it's 200
        return response($result,400);
    }

    public static function deleteFile(Request $request,Manager $manager)
    {
        $fileName = $request->get('fileName');
        $result = $manager->deleteFile($fileName);

        if($result['success'])
            return $result;
        return response($result,400);
    }

    public static function cropImage(Request $request,Manager $manager)
    {
        $fileName = $request->get('fileName');
        $cropData = $request->get('cropData');
        $result = $manager->cropImage($fileName,
            $cropData['x'],
            $cropData['y'],
            $cropData['width'],
            $cropData['height']
        );

        if($result['success'])
            return $result;
        return response($result,400);
    }

    public static function cropImageStatic(Request $request,Manager $manager,array $d = [])
    {
        $fileName = $request->get('fileName');
        $cropData = $request->get('cropData');

        if(Arr::has($d,['width','height']))
        {
            $result = AspectRatioChecker::f(Arr::get($d,'width'),Arr::get($d,'height'),$cropData['width'],$cropData['height']);
            if(!$result)
                throw new \Exception('bad aspect ratio');
        }

        $result = $manager->cropImage($fileName,
            $cropData['x'],
            $cropData['y'],
            $cropData['width'],
            $cropData['height']
        );

        if($result['success'])
            return $result;
        return response($result,400);
    }

    public static function copyImageIntoNewModel(Model $oldModel,Model $newModel,Manager $manager) : void
    {
        $manager->copyImageIntoNewModel($oldModel,$newModel);
    }

}
