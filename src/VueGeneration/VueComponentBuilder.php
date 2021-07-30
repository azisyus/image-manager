<?php


namespace Azizyus\ImageManager\VueGeneration;


use Azizyus\ImageManager\Manager;

class VueComponentBuilder
{

    public static function build(Manager $m)
    {
        $data = [

            'filesUrl' => $m->getFilesUrl(),
            'uploadUrl' => $m->getUploadUrl(),
            'sortFilesUrl' => $m->getSortUrl(),
            'cropFilesUrl' => $m->getCropFilesUrl(),
            'remoteUploadUrl' => $m->getRemoteUploadUrl(),
            'specialImagesUrl' => $m->getSpecialImagesUrl(),
            'chooseSpecialImageUrl' => $m->getChooseSpecialImageUrl(),

        ];
        return view('ImageManager::vue')->with($data);
    }

}
