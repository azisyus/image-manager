<?php

namespace Azizyus\ImageManager\Helper;


class AspectRatioChecker
{
    public static function f($r1,$r2,$q1,$q2) : bool
    {
        $t1 = (float)$r1/(float)$r2;
        $t2 = (float)$q1/(float)$q2;
        $t1 = round($t1,3);
        $t2 = round($t2,3);
        return $t1 - $t2 < 0.010;
    }
}
