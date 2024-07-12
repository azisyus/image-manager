<?php


namespace Azizyus\ImageManager;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\DB\Repository;
use Azizyus\ImageManager\Exceptions\RecordDoesNotExist;
use Azizyus\ImageManager\Naming\Generators;
use Fusonic\OpenGraph\Consumer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use Azizyus\ImageManager\Helper\AspectRatioChecker;


class Manager
{

    /**
     * @var FilesystemAdapter
     */
    protected $adapter;
    /**
     * @var
     */
    protected $deleteUrl;
    /**
     * @var
     */
    protected $uploadUrl;
    /**
     * @var
     */
    protected $filesUrl;
    /**
     * @var Repository
     */
    protected $repository;
    /**
     * @var
     */
    protected $sortUrl;
    /**
     * @var
     */
    protected $cropFilesUrl;
    /**
     * @var string
     */
    protected $validation = 'mimes:jpeg,jpg,png,bmp,svg|max:1024';

    /**
     * @var String
     */
    protected $chooseSpecialImageUrl = null;

    /**
     * @var String
     */
    protected $specialImagesUrl = null;

    /**
     * @var int
     */
    protected $uploadImageLimit = 15;

    /**
     * @return int
     */
    public function getUploadImageLimit(): int
    {
        return $this->uploadImageLimit;
    }

    /**
     * @param int $uploadImageLimit
     */
    public function setUploadImageLimit(int $uploadImageLimit): void
    {
        $this->uploadImageLimit = $uploadImageLimit;
    }

    /**
     * @return String
     */
    public function getChooseSpecialImageUrl(): ?string
    {
        return $this->chooseSpecialImageUrl;
    }

    /**
     * @param String $chooseSpecialImageUrl
     */
    public function setChooseSpecialImageUrl(?string $chooseSpecialImageUrl): void
    {
        $this->chooseSpecialImageUrl = $chooseSpecialImageUrl;
    }

    /**
     * @return String
     */
    public function getSpecialImagesUrl(): ?string
    {
        return $this->specialImagesUrl;
    }

    /**
     * @param String $specialImagesUrl
     */
    public function setSpecialImagesUrl(?string $specialImagesUrl): void
    {
        $this->specialImagesUrl = $specialImagesUrl;
    }



    /**
     * @var
     */
    protected $remoteUrlUploadUrl;

    /**
     * @var \Illuminate\Support\Collection|null
     */
    protected $maintainableVariations = null;


    /**
     * @var array|Collection
     */
    protected $specialImageDefinitions = [];

    /**
     * @var callable|null
     */
    protected $nameGenerator = null;

    /**
     * Manager constructor.
     * @param FilesystemAdapter $adapter
     */
    public function __construct(FilesystemAdapter $adapter)
    {
        $this->adapter = $adapter;
        $this->repository = new Repository();
        $this->maintainableVariations = collect();
        $this->specialImageDefinitions = collect();
        $this->setNameGenerator(Generators::unique());
        $this->defineVariation('zoneThumbnail',150,150,'zoneThumbnail');
    }

    public function setNameGenerator(callable $f)
    {
        $this->nameGenerator = $f;
    }

    /**
     * @param FilesystemAdapter $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param string $key
     * @param int $width
     * @param int $height
     * @deprecated
     */
    public function defineSpecialImage(string $key, int $width, int $height)
    {
        $this->specialImageDefinitions->put($key,[
            'width'  => $width,
            'height' => $height,
            'cropAspectRestricted' => false,
        ]);

        $this->maintainableVariations->put($key,[
            'width'  =>$width,
            'height' => $height,
            'type'  => $key,
        ]);

    }

    /**
     * @param string $key
     * @param int $width
     * @param int $height
     */
    public function defineSpecialImageWithArrayOptions(string $key,array $data)
    {
        $width = Arr::get($data,'width');
        $height = Arr::get($data,'height');
        $cropAspectRestricted = Arr::get($data,'cropAspectRestricted',false);
        $targetExtension = Arr::get($data,'targetExtension',null);
        $noCanvas = Arr::get($data,'noCanvas',false);

        $this->specialImageDefinitions->put($key,[
            'width'  => $width,
            'height' => $height,
            'cropAspectRestricted' => $cropAspectRestricted,
            'targetExtension' => $targetExtension,
        ]);

        $this->maintainableVariations->put($key,[
            'width'  => $width,
            'height' => $height,
            'type'   => $key,
            'noCanvas' => $noCanvas,
            'targetExtension' => $targetExtension,
        ]);

    }

    /**
     * @param string $type
     * @return array
     */
    public function getImageByType(string $type) : array
    {
        return $this->map()($this->repository->getByType($type));
    }

    /**
     * @param string $type
     * @param string $fileName
     * @return ManagedImage
     */
    protected function generateSpecialImage(string $type, string $fileName) : ManagedImage
    {
        /**
         * @var ManagedImage $imageRecord
         */
        $imageRecord = $this->repository->getByFileName($fileName);
        $newOriginalFileName = $this->generateRandomFileName($imageRecord->extension);
        $newFileName = $this->generateRandomFileName($imageRecord->extension);

        $this->adapter->copy($imageRecord->originalFileName,$newFileName);
        $this->adapter->copy($imageRecord->originalFileName,$newOriginalFileName);

        $oldSpecialImage = $this->repository->getByType($type);
        if($oldSpecialImage)
            $this->deleteFile($oldSpecialImage->fileName);

        $q = $this->repository->createImage($newFileName,$newOriginalFileName,$imageRecord->size,$imageRecord->extension,$type);
        $this->maintainVariations($q->fileName);
        return $q;
    }

    /**
     * @param string $type
     * @param string $fileName
     * @return array
     */
    public function chooseSpecialImage(string $type, string $fileName) : array
    {
        $imageRecord = $this->generateSpecialImage($type,$fileName);
        return array_merge([
            'success' => true,
            'error' => null,
        ],$this->map()($imageRecord));
    }

    /**
     * @return array
     */
    public function specialImages() : array
    {
        $types = $this->specialImageDefinitions->keys()->toArray();
        $specialImages = $this->repository->getByTypes($types);
        $r = $this->specialImageDefinitions->map(function(array $s, $type)use($specialImages){
            $foundImage = $specialImages->where('type',$type)->first();
            $m = [];
            if($foundImage)
                $m = $this->map()($foundImage);
            return array_merge(['cropAspectRestricted'=>$s['cropAspectRestricted'],'width'=>$s['width'],'height'=>$s['height'],'type'=>$type,'title'=>$type,'image'=>$m]);
        })->values();
        return [
            'success' => true,
            'error' => null,
            'specialImages' => $r
        ];
    }

    /**
     * @param Repository $repository
     */
    public function setRepository(Repository $repository) : void
    {
        $this->repository = $repository;
    }

    /**
     * @return Repository
     */
    public function getRepository() : Repository
    {
        return $this->repository;
    }

    /**
     * @param string $key
     * @param int|null $width
     * @param int|null $height
     * @deprecated
     */
    public function defineVariation(string $key, int $width = null, int $height = null,string $type)
    {
        $this->maintainableVariations->put($key,[
            'width'  => $width,
            'height' => $height,
            'type'   => $type??'gallery',
        ]);
    }


    /**
     * @param string $key
     * @param array $params
     * $params = [
     *      'width'         => (string) image width.
     *      'height'        => (string) image height.
     *      'type'          => (string) image type.
     *      'noCanvas'      => (bool)   generates image without centered canvas
     *   ]
     */
    public function defineVariationImageWithOptions(string $key,array $params=[])
    {
        $type = Arr::get($params,'type','gallery');
        $this->maintainableVariations->put($key,array_merge($params,[
            'type' => $type
        ]));
    }

    /**
     * @param $fileName
     * @return ManagedImage|null
     */
    public function getFileRecord($fileName) : ?ManagedImage
    {
        $r = $this->repository->getByFileName($fileName);
        if(!$r)
            throw new RecordDoesNotExist();
        return $r;
    }

    /**
     * @param string $key
     */
    public function removeVariation(string $key) : void
    {
        $this->maintainableVariations->forget($key);
    }

    /**
     * @param string $fileName
     * @return \Illuminate\Support\Collection
     * @throws Exceptions\FileRecordDoesntExist
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function maintainVariations(string $fileName) : Collection
    {
        $file = $this->adapter->get($fileName);
        /**
         * @var ManagedImage $imageRecord
         */
        $imageRecord = $this->repository->getByFileName($fileName);

        $r = $this->maintainableVariations
            ->whereIn('type',[$imageRecord->type,'global','zoneThumbnail'])
            ->map(function(array $variation)use($file,$imageRecord){


                $noCanvas = Arr::get($variation,'noCanvas',false);
                $targetExtension = $imageRecord->extension !== 'svg' ? Arr::get($variation,'targetExtension',null) : 'svg';
                $newVariationImageName = $this->generateRandomFileName($targetExtension??$imageRecord->extension);
                if($imageRecord->extension === 'svg')
                    $resized = $file;
                else
                {
                    $resized = call_user_func_array(function(bool $nocanvas,$variation,$imageRecord,$file,$targetExtension)
                    {

                        /**
                         * @var \Intervention\Image\Image $canvas
                         * @var \Intervention\Image\Image $resized
                         * @var ManagedImage $imageRecord
                         */



                        $resized = Image::make($file)->resize($variation['width'],$variation['height'],function($c){
                            $c->aspectRatio();
                        });

                        if($nocanvas) //no absolute canvas just return
                            return $resized->encode($targetExtension??$imageRecord->extension)->getEncoded();

                        //import insert into canvas to achieve absolute width-height for everyimage
                        $canvas = Image::canvas($variation['width'],$variation['height']);
                        return $canvas->insert($resized,'center')
                            ->encode($targetExtension??$imageRecord->extension)
                            ->getEncoded();

                    },[$noCanvas,$variation,$imageRecord,$file,$targetExtension]);
                }


                $this->adapter->put(
                  $newVariationImageName,
                  $resized,[
                        'ContentType' => $this->adapter->mimeType($imageRecord->fileName),
                    ]
                );

                return $newVariationImageName;
        });

        //delete old variations
        array_map(function($fileName){
            $this->adapter->delete($fileName);
        },$imageRecord->variations);
        //set new ones
        $this->repository->setVariations($fileName,$r->toArray());

        return $r;
    }


    /**
     * @param string $s
     */
    public function setRemoteUrlUploadUrl(string $s)  : void
    {
        $this->remoteUrlUploadUrl = $s;
    }

    /**
     * @return string
     */
    public function getRemoteUploadUrl() : string
    {
        return $this->remoteUrlUploadUrl;
    }

    /**
     * @param string $s
     */
    public function setValidation(string $s)  : void
    {
        $this->validation = $s;
    }

    /**
     * @return string
     */
    public function getValidation() : string
    {
        return $this->validation;
    }

    /**
     * @param string $url
     */
    public function setCropFilesUrl(string $url) : void
    {
        $this->cropFilesUrl = $url;
    }

    /**
     * @return string
     */
    public function getCropFilesUrl() : string
    {
        return $this->cropFilesUrl;
    }

    /**
     * @param string $sortUrl
     */
    public function setSortUrl(string $sortUrl) : void
    {
         $this->sortUrl = $sortUrl;
    }

    /**
     * @return string
     */
    public function getSortUrl() : string
    {
        return $this->sortUrl;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getFiles()
    {
        return $this->repository->all()->map($this->map());
    }

    /**
     * @return \Closure
     */
    public function map()
    {
        return function(ManagedImage $image){
            $driver = $image::getStorageDriver();
            $image::setStorageDriver($this->adapter);
            $q = ManagedImage::mapper()($image);
            $image::setStorageDriver($driver);
            return array_merge($q,[
                'deleteUrl' => $this->deleteUrl,
                'cropUrl' => $this->cropFilesUrl,
                'cropable' => $image->extension !== 'svg',
            ]);
        };
    }

    /**
     * @param string $fileName
     * @return false|string
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function readFile(string $fileName)
    {
        return $this->adapter->read($fileName);
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function generateFileUrl(string $fileName) : string
    {
        return $this->adapter->url($fileName);
    }

    /**
     * @param string $fileName
     * @return array
     */
    public function deleteFile(string $fileName) : array
    {
        /**
         * @var ManagedImage $imageRecord
         */
        $imageRecord = $this->repository->getByFileName($fileName);
        if(!$imageRecord)
            throw new RecordDoesNotExist();

        $this->repository->deleteFileByName($imageRecord->fileName);
        $this->adapter->delete($imageRecord->fileName);
        $this->adapter->delete($imageRecord->originalFileName);

        return ['success' => true];
    }

    /**
     * @param UploadedFile $file
     * @return array
     */
    public function upload(UploadedFile $file) : array
    {

        Validator::validate([
            'file' => $file,
            'totalImageCount' => $this->getRepository()->getModelImageCount(),
        ],[
            'file' =>  $this->getValidation(),
            'totalImageCount' => function($name,$value,$fail)
            {
                $limit = $this->getUploadImageLimit();
                if($value+1 > $limit)

                    $fail(trans('imageValidation.uploadLimit',['limit'=>$limit]));
            }
        ]);

        $fileName = $this->generateRandomFileName($file->extension());
        $originalFileName = $this->generateRandomFileName($file->extension());

        $this->adapter->put($fileName,$file->getContent(),[
            'ContentType' => $file->getMimeType() == 'image/svg' ? 'image/svg+xml' : $file->getMimeType(),
        ]);
        $this->adapter->put($originalFileName,$file->getContent(),[
            'ContentType' => $file->getMimeType() == 'image/svg' ? 'image/svg+xml' : $file->getMimeType(),
        ]);

        $image = $this->repository->createImage($fileName,$originalFileName,$file->getSize(),$file->extension(),'gallery');
        $this->maintainVariations($image->fileName);
        $image->refresh();
        return array_merge([
            'success' => true,
            'error' => null,
        ],$this->map()($image));
    }

    /**
     * @param string $fileName
     * @param string $variationFileName
     * @param int|null $width
     * @param int|null $height
     * @return string
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function generateVariation(string $fileName, string $variationFileName, int $width = null, int $height = null) : string
    {
        $image = Image::make($this->adapter->read($fileName))->fit($width,$height);
        $this->adapter->put($variationFileName,$image->getEncoded(),[
            'ContentType' => $this->adapter->getMimetype($fileName),
        ]);
        return $variationFileName;
    }

    /**
     * @param string $extension
     * @return string
     */
    private function generateRandomFileName(string $extension) : string
    {
        $f = $this->nameGenerator;
        if($f)
            return $f($extension);
        else
            throw new \Exception('no name generator found');
    }

    /**
     * @param string $deleteUrl
     */
    public function setDeleteUrl(string $deleteUrl)
    {
        $this->deleteUrl = $deleteUrl;
    }

    /**
     * @param string $uploadUrl
     */
    public function setUploadUrl(string $uploadUrl)
    {
        $this->uploadUrl = $uploadUrl;
    }

    /**
     * @param string $filesUrl
     */
    public function setFilesUrl(string $filesUrl)
    {
        $this->filesUrl = $filesUrl;
    }

    /**
     * @return string
     */
    public function getFilesUrl() : string
    {
        return $this->filesUrl;
    }

    /**
     * @return string
     */
    public function getUploadUrl() : string
    {
        return $this->uploadUrl;
    }

    /**
     * @param array $filesNames
     */
    public function setSort(array $filesNames=[]) : array
    {
        foreach ($filesNames as $sort => $id)
            $this->repository->updateSort($id,$sort);

        return ['success'=>true];
    }

    /**
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function withIds(array $ids=[])
    {
        return $this->repository->withIds($ids)->get()->map($this->map());
    }

    /**
     * @param string $fileName
     * @param $x
     * @param $y
     * @param $width
     * @param $height
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function cropImage(string $fileName, $x, $y, $width, $height) : array
    {

        /**
         * @var ManagedImage $imageRecord
         */
        $imageRecord = $this->repository->getByFileName($fileName);

        //for now only special images which has crop restriction
        $specImage = Arr::first($this->specialImageDefinitions,function(array $x,$k)use($imageRecord){
            return $imageRecord->type == $k;
        });
        //some images are obviously forced to be in specific aspectRatio
        //we simply check them here to make sure the frontend cropper didn't get manipulated by user
        //so we simply make sure it was a right crop with required aspectRatio
        if($specImage && $specImage['cropAspectRestricted'])
        {
            $aspectEquality = AspectRatioChecker::f($width,$height,$specImage['width'],$specImage['height']);
            if(!$aspectEquality)
                throw new \Exception('sent cropper coordinates does not corresponds to special image\'s aspect ration');
        }
        $c = Image::make($this->adapter->get($imageRecord->originalFileName)); //crop via original image
        $c->crop((int)$width,(int)$height,(int)$x,(int)$y);
        $this->adapter->put($fileName,$c->encode(),[
            'ContentType' => $this->adapter->mimeType($imageRecord->originalFileName)
        ]);
        $newFileName = $this->generateRandomFileName($imageRecord->extension);
        $imageRecord->fileName = $newFileName;
        $this->adapter->rename($fileName,$newFileName);
        $imageRecord->save();
        $this->maintainVariations($imageRecord->fileName);
        $imageRecord->refresh();
        return array_merge([
            'success' => true,
            'error' => null,
        ],$this->map()($imageRecord));
    }

    /**
     * @param string $url
     * @return array
     * @throws \Exception
     */
    public function importFromUrl(string $url) : array
    {

        Validator::validate(['url' => $url],['url' => 'url']);

        $baseName = basename($url);
        $extension = pathinfo($baseName,PATHINFO_EXTENSION);
        if($extension) //single image
            return [
                'success' => true,
                'images' => [
                    $this->importSingleImage($url)
                ]
            ];
        else
        {

            $detectedImages = call_user_func(function(string $url){

                $consumer = new Consumer(new Client(), new HttpFactory());
                $object = $consumer->loadUrl($url);
                return array_map(function($image) {
                    return $image->url;
                },$object->images);

            },$url);



            Validator::validate([
                'totalImageCount' => $this->getRepository()->getModelImageCount(),
            ],[
                'totalImageCount' => function($name,$value,$fail) use($detectedImages)
                {
                    $limit = $this->getUploadImageLimit();
                    if($value+count($detectedImages) > $limit)
                        $fail('you can only upload '.$limit.' image');
                }
            ]);

            $uploadedFiles = [];
            foreach ($detectedImages as $detectedImage)
            {
                $guzzle = (new Client())->get($detectedImage);
                $fileSize = Arr::first($guzzle->getHeader('Content-Length'));
                //$fileSize  < 2mb
                if($fileSize < 2000000)
                    $uploadedFiles[] = $this->importSingleImage($detectedImage);
            }
            return [
                'success' => true,
                'images' => $uploadedFiles
            ];
        }


    }

    private function importSingleImage(string $url)
    {

        $tmpfname = tempnam("/tmp", "UL_IMAGE");
        $file = null;

        try
        {
            $response = (new Client())->get($url);
            $file = $response->getBody()->getContents();

        }
        catch (\Exception $exception)
        {
            //is it a error from guzzle, 403 or bad file idk
            if($exception instanceof TransferException)
                throw ValidationException::withMessages([
                    'url'  => 'failed to fetch url',
                ]);
            else //is it something else?
            {
                throw $exception;
            }

        }

        file_put_contents($tmpfname,$file);
        $u = new UploadedFile($tmpfname,"UL_IMAGE",mime_content_type($tmpfname),UPLOAD_ERR_OK,true);

        Validator::validate([
            'file' => $u,
        ],[
            'file' => $this->validation
        ]);

        return $this->upload($u);
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function checkFileExist(string $fileName) : bool
    {
        return $this->adapter->exists($fileName);
    }

    /**
     * @return callable
     */
    public function copyImage() : callable
    {
        return function(string $fileName,string $extension = null) {
            if(!$extension)
                $extension = pathinfo($fileName,PATHINFO_EXTENSION);
            $newFileName = $this->generateRandomFileName($extension);
            $this->adapter->copy($fileName,$newFileName);
            return $newFileName;
        };
    }

    public function copyImageIntoNewModel(Model $oldModel,Model $newModel) : void
    {
        $allImages = $oldModel->wholeImages()->get();

        $generateNewFileNameByExtension = $this->copyImage();

        /**
         * @var ManagedImage $image
         */
        foreach ($allImages as $image)
        {
            $newManagedImage = $image->replicate();
            $newManagedImage->fileName = $generateNewFileNameByExtension($image->fileName,$image->extension);
            $newManagedImage->originalFileName = $generateNewFileNameByExtension($image->originalFileName,$image->extension);

            $newManagedImage->variations = call_user_func(function(array $variations)use($generateNewFileNameByExtension){
                $x=[];
                foreach ($variations as $key => $v)
                    $x[$key] = $generateNewFileNameByExtension($v,'jpg');
                return $x;
            },$newManagedImage->variations);

            $newModel->wholeImages()->updateOrCreate(
                ['relatedModelId'=>$newModel->id,'relatedModel'=>get_class($newModel),'fileName'=>$newManagedImage->fileName],
                array_merge($newManagedImage->attributesToArray(),['relatedModelId'=>$newModel->id,'relatedModel'=>get_class($newModel)]));



        }

    }

    public function updateAltText($fileName, $altText)
    {
        $image = $this->repository-setAlt($fileName, $altText);

        return ['success' => true, 'data' => $image];
    }

}
