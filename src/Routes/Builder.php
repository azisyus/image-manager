<?php

namespace Azizyus\ImageManager\Routes;


use Illuminate\Support\Facades\Route;

class Builder
{


    public static function routes($controller='UploadController')
    {
        Route::get('uploader',$controller.'@index')->name('index');
        Route::any('specialImages',$controller.'@specialImages')->name('specialImages');
        Route::any('chooseSpecialImage',$controller.'@chooseSpecialImage')->name('chooseSpecialImage');
        Route::any('upload',$controller.'@upload')->name('upload');
        Route::any('listing',$controller.'@listing')->name('listing');
        Route::any('sort',$controller.'@sort')->name('sort');
        Route::any('remote',$controller.'@remote')->name('remote');
        Route::any('delete',$controller.'@delete')->name('delete');
        Route::any('crop',$controller.'@crop')->name('crop');
        Route::any('files',$controller.'@files')->name('files');
    }

}
