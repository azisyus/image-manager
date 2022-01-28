<?php

namespace Azizyus\ImageManager\Tests;

use Azizyus\ImageManager\Helper\AspectRatioChecker;

class AspectRationTest extends BaseTestCase
{

    public function testAspectRation()
    {
        $t = AspectRatioChecker::f(6.99,6.99,2.33,2.33);
        $this->assertTrue($t);
    }

}
