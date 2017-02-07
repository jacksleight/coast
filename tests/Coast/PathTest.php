<?php
namespace Coast\Test;

use Coast\Path;

class PathTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('/dir/base.ext', (string) $path);
        $this->assertEquals('/dir/base.ext', $path->name());
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

        $this->assertEquals('/one/four', $a->toAbsolute($b)->name());
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

        $this->assertEquals('../four', $a->toRelative($b)->name());
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

    public function testReal()
    {
        $path = new Path(__DIR__ . '/..');
        $real = $path->toReal();

        $this->assertEquals($real->name(), realpath(__DIR__ . '/..'));

        $path = new Path('./');
        $this->setExpectedException('Exception');
        $path->toReal();
    }

    public function testChild()
    {
        $path = new Path('/one/two/three');

        $this->assertEquals('/one/two/three/four', $path->child('four')->name());
    }

    public function testParent()
    {
        $path = new Path('/one/two/three');

        $this->assertEquals('/one/two', $path->parent()->name());
    }

    public function testDir()
    {
        $path = new Path('/one/two/three');
        $dir  = $path->toDir();

        $this->assertInstanceOf('Coast\Dir', $dir);
        $this->assertEquals('/one/two/three', $dir->name());
    }

    public function testFile()
    {
        $path = new Path('/one/two/three');
        $file = $path->toFile();

        $this->assertInstanceOf('Coast\File', $file);
        $this->assertEquals('/one/two/three', $file->name());
    }
}