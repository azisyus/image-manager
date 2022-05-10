<?php

namespace Azizyus\ImageManager\Events;


use Azizyus\ImageManager\DB\Models\ManagedImage;
use Azizyus\ImageManager\Manager;
use Illuminate\Database\Eloquent\Builder;

class Events
{

    /**
     * @param callable $f
     * @return void
     *
     * leaving empty for bc i don't really want to refactor old code
     *
     * @deprecated
     */
    public static function register(callable $f)
    {

    }



}
