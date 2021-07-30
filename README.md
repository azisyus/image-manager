# IMAGE MANAGER (for Laravel)




## What does this package can do?

* Upload images (Drag drop or directly selecting from window)
* Sorting images by drag drop
* Can create and maintain specific variations of images (recreation is possible)
* Crop images and crop variations
* Choose variations from already uploaded images
* Define variations and specific images like "Thumbnail" or "Cover" from uploaded images
* Model attachment and query restrictions possible 
* Can import image from URL, it will behave like normally uploaded file



## INSTALLATION

<code>composer require azizyus/image-manager ^1.0</code>

Add to your config/app.php <br>
```\Azizyus\ImageManager\ImageManagerServiceProvider::class,``` <br>

```php artisan migrate``` <br>
```php artisan vendor:publish --tag=managed-images``` <br>


## NPM DEPENDENCIES
```
        "bootstrap-vue": "^2.21.2",
        "vue-cropperjs": "^4.2.0",
        "vuedraggable": "^2.24.3"
```

## EXAMPLES

### CONTROLLER




```  
        public function listing()
        {
            $user = User::with('allImages','thumbnailImage','coverImage')->first();
            $data = [
                'images' => $user->allImages->map(function ($f){return $f->map();}),
                'thumbnailImage' => $user->thumbnailImage->map(),
                'coverImage' => $user->coverImage->map(),

            ];
            //dd($data['coverImage'],$data['thumbnailImage'],$data['images'][0]);
            return view('listing')->with($data);
        }

        public function index()
        {
            ImageManager::setUploadUrl(route('image.upload'));
            ImageManager::setFilesUrl(route('image.files'));
            return view('uploader');
        }

        public static function chooseSpecialImage(Request $request)
        {

            $user = User::first();
            return ImageManager::withModel($user,function()use($request){
                return ImageManager::chooseSpecialImage($request);
            });
        }

        public static function specialImages(Request $request)
        {
            $user = User::first();
            return ImageManager::withModel($user,function()use($request){
                return ImageManager::specialImages($request);
            });
        }

        public function upload(Request $request)
        {
            $user = User::first();
            return ImageManager::withModel($user,function()use($request){
                return ImageManager::upload($request);
            });
        }

        public function delete(Request $request)
        {
            $user = User::first();
            return ImageManager::withModel($user,function()use($request){
                return ImageManager::deleteFile($request);
            });
        }

        public function sort(Request $request)
        {
            $user = User::first();
            return ImageManager::withModel($user,function()use($request){
                return ImageManager::setSort($request);
            });
        }

        public function crop(Request $request)
        {
            $user = User::first();
            return ImageManager::withModel($user,function()use($request){
                return ImageManager::cropImage($request);
            });
        }

        public function remote(Request $request)
        {
            $user = User::first();
            return ImageManager::withModel($user,function()use($request){
                return ImageManager::importFromUrl($request);
            });
        }

        public function files()
        {
            $user = User::first();
            return ImageManager::withModel($user,function(){
                return ImageManager::getFiles();
            });
        }
```


### ROUTES
```
    Route::get('uploader','UploadController@index')->name('image.index');
    Route::any('specialImages','UploadController@specialImages')->name('image.specialImages');
    Route::any('chooseSpecialImage','UploadController@chooseSpecialImage')->name('image.chooseSpecialImage');
    Route::any('upload','UploadController@upload')->name('image.upload');
    Route::any('listing','UploadController@listing')->name('image.listing');
    Route::any('sort','UploadController@sort')->name('image.sort');
    Route::any('remote','UploadController@remote')->name('image.remote');
    Route::any('delete','UploadController@delete')->name('image.delete');
    Route::any('crop','UploadController@crop')->name('image.crop');
    Route::any('files','UploadController@files')->name('image.files');
```


### ROUTE ATTACHMENT
```
    $this->app->singleton('imageManager',function(){
                $s = new Manager(Storage::disk('public'));
                $s->setDeleteUrl('/delete');
                $s->setUploadUrl('/upload');
                $s->setFilesUrl('/files');
                $s->setSortUrl('/sort');
                $s->setCropFilesUrl('/crop');
                $s->setRemoteUrlUploadUrl('/remote');
                $s->setSpecialImagesUrl('/specialImages');
                $s->setChooseSpecialImageUrl('/chooseSpecialImage');
                return $s;
    });
```


### VARIATION DEFINITION
```
        ImageManager::defineSpecialImage('thumbnail',150,150);
        ImageManager::defineSpecialImage('cover',150,150);
        ImageManager::defineVariation('sliderListingImage',75,75,'gallery');
```

### RE-GENERATE Variations

it would be useful in case of you define new stuff
```php artisan imagemanager:generate:variations```

