<?php

namespace Azizyus\ImageManager\Tests;



use Azizyus\ImageManager\Naming\Generators;

class NameGeneratorTest extends BaseTestCase
{

    public function testRandomGen()
    {
        $x = Generators::unique();
        $this->assertNotEquals($x('jpg'),$x('jpg'));
    }

    public function testRandomExtension()
    {
        $x = Generators::unique();
        $this->assertTrue(str_ends_with($x('webp'),'.webp'));
        $this->assertTrue(str_ends_with($x('jpg'),'.jpg'));
    }

    public function testForceGen()
    {
        $x = Generators::forced('webp');
        $this->assertNotEquals($x(),$x());
        $this->assertTrue(str_ends_with($x(),'.webp'));
    }
}
