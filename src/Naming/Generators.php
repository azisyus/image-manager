<?php

namespace Azizyus\ImageManager\Naming;

class Generators
{

    public static function unique() : callable
    {
        return function($extension){
            return uniqid().'.'.$extension;
        };
    }

    public static function forced(string $extension) : callable
    {
        return function() use($extension) {
            return uniqid().'.'.$extension;
        };
    }

}
