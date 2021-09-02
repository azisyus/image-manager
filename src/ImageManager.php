<?php


namespace Azizyus\ImageManager;


use Azizyus\ImageManager\DB\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Validator;
use function response;

class ImageManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'imageManager';
    }

    public static function putModel(Model $model = null)
    {
        self::getFacadeRoot()->getRepository()->setModel($model);
    }

    public static function withModel(Model $model,callable $w)
    {
        self::getFacadeRoot()->getRepository()->setModel($model);
        $result = $w();
        self::getFacadeRoot()->getRepository()->setModel(null);
        return $result;
    }

    public static function chooseSpecialImage(Request $request)
    {
        $type = $request->get('type');
        $fileName = $request->get('fileName');
        $r = self::getFacadeRoot()->chooseSpecialImage($type,$fileName);
        if($r['success'])
            return $r;
        return response($r,400);
    }

    public static function specialImages()
    {
        $r = self::getFacadeRoot()->specialImages();
        if($r['success'])
            return $r;
        return response($r,400);
    }

    public static function importFromUrl(Request $request)
    {
        $url = $request->get('url');
        $result = self::getFacadeRoot()->importFromUrl($url);

        if($result['success'])
            return $result; // simply it's 200
        return response($result,400);
    }

    public static function setSort(Request $request)
    {
        $filesNames = $request->get('fileNames',[]);
        $result = self::getFacadeRoot()->setSort($filesNames);
        if($result['success'])
            return $result;
        return response($result,400);
    }

    public static function upload(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'file' =>  self::getFacadeRoot()->getValidation(),
        ]);

        if($validator->fails())
            return  response(self::getFacadeRoot()->makeNotFileFail(),400);

        $file = $request->file('file');
        $result = self::getFacadeRoot()->upload($file);

        if($result['success'])
            return $result; // simply it's 200
        return response($result,400);
    }

    public static function deleteFile(Request $request)
    {
        $fileName = $request->get('fileName');
        $result = self::getFacadeRoot()->deleteFile($fileName);

        if($result['success'])
            return $result;
        return response($result,400);
    }

    public static function cropImage(Request $request)
    {
        $fileName = $request->get('fileName');
        $cropData = $request->get('cropData');
        $result = self::getFacadeRoot()->cropImage($fileName,
            $cropData['x'],
            $cropData['y'],
            $cropData['width'],
            $cropData['height']
        );

        if($result['success'])
            return $result;
        return response($result,400);
    }

    public static function copyImageIntoNewModel(Model $oldModel,Model $newModel) : void
    {
        self::getFacadeRoot()->copyImageIntoNewModel($oldModel,$newModel);
    }

}
