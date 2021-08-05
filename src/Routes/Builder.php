<?php

namespace Azizyus\ImageManager\Routes;


use Illuminate\Support\Facades\Route;

class Builder
{


    public static function routes()
    {
        Route::get('uploader','UploadController@index')->name('index');
        Route::any('specialImages','UploadController@specialImages')->name('specialImages');
        Route::any('chooseSpecialImage','UploadController@chooseSpecialImage')->name('chooseSpecialImage');
        Route::any('upload','UploadController@upload')->name('upload');
        Route::any('listing','UploadController@listing')->name('listing');
        Route::any('sort','UploadController@sort')->name('sort');
        Route::any('remote','UploadController@remote')->name('remote');
        Route::any('delete','UploadController@delete')->name('delete');
        Route::any('crop','UploadController@crop')->name('crop');
        Route::any('files','UploadController@files')->name('files');
    }

}
