<?php

namespace Azizyus\ImageManager\Helper;


class AspectRatioChecker
{
    public static function f($r1,$r2,$q1,$q2) : bool
    {
        return (float)$r1/(float)$r2 === (float)$q1/(float)$q2;
    }
}
