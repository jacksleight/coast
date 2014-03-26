<?php
namespace Coast;

use Coast\Path;

class PathTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('/dir/base.ext', (string) $path);
        $this->assertEquals('/dir/base.ext', $path->toString());
    }

    public function testDirName()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('/dir', $path->dirName());
        $path->dirName('/other');
        $this->assertEquals('/other/base.ext', $path->name());
    }

    public function testBaseName()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('base.ext', $path->baseName());
        $path->baseName('image.jpg');
        $this->assertEquals('/dir/image.jpg', $path->name());
    }

    public function testFileName()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('base', $path->fileName());
        $path->fileName('image');
        $this->assertEquals('/dir/image.ext', $path->name());
    }

    public function testExtName()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('ext', $path->extName());
        $path->extName('jpg');
        $this->assertEquals('/dir/base.jpg', $path->name());
    }

    public function testPrefix()
    {
        $path = new Path('/dir/base.ext');
        $path->prefix('new-');
        $this->assertEquals('/dir/new-base.ext', $path->name());
    }

    public function testSuffix()
    {
        $path = new Path('/dir/base.ext');
        $path->suffix('-new');
        $this->assertEquals('/dir/base-new.ext', $path->name());
    }

    public function testWithin()
    {
        $a = new Path('/parent');
        $b = new Path('/parent/child');

        $this->assertTrue($b->isWithin($a));
        $this->assertFalse($a->isWithin($b));
    }

    public function testAbsolute()
    {
        $a = new Path('../four');
        $b = new Path('/one/two/three');

        $this->assertEquals('/one/four', $a->toAbsolute($b)->toString());
    }

    public function testAbsoluteException()
    {
        $a = new Path('../four');
        $b = new Path('/one/two/three');

        $this->setExpectedException('Exception');
        $b->toAbsolute($a);
    }

    public function testRelative()
    {
        $a = new Path('/one/four');
        $b = new Path('/one/two/three');

        $this->assertEquals('../four', $a->toRelative($b)->toString());
    }

    public function testRelativeException()
    {   
        $a = new Path('/one/four');
        $b = new Path('../');

        $this->setExpectedException('Exception');
        $a->toRelative($b);
    }

    public function testType()
    {
        $abs = new Path('/name');
        $rel = new Path('name');

        $this->assertTrue($abs->isAbsolute());
        $this->assertTrue($rel->isRelative());
    }
}