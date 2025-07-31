<?php

namespace Coast\Test;

use Coast\Path;

class PathTest extends \PHPUnit\Framework\TestCase
{
    public function test_name()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('/dir/base.ext', (string) $path);
        $this->assertEquals('/dir/base.ext', $path->name());
    }

    public function test_dir_name()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('/dir', $path->dirName());
        $path->dirName('/other');
        $this->assertEquals('/other/base.ext', $path->name());
    }

    public function test_base_name()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('base.ext', $path->baseName());
        $path->baseName('image.jpg');
        $this->assertEquals('/dir/image.jpg', $path->name());
    }

    public function test_file_name()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('base', $path->fileName());
        $path->fileName('image');
        $this->assertEquals('/dir/image.ext', $path->name());

        $path = new Path('/dir/base.sub.ext');

        $this->assertEquals('base.sub', $path->fileName());
        $this->assertEquals('base', $path->fileNameDot());
    }

    public function test_ext_name()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('ext', $path->extName());
        $path->extName('jpg');
        $this->assertEquals('/dir/base.jpg', $path->name());

        $path = new Path('/dir/base.sub.ext');

        $this->assertEquals('ext', $path->extName());
        $this->assertEquals('sub.ext', $path->extNameDot());
    }

    public function test_prefix()
    {
        $path = new Path('/dir/base.ext');
        $path->prefix('new-');
        $this->assertEquals('/dir/new-base.ext', $path->name());
    }

    public function test_suffix()
    {
        $path = new Path('/dir/base.ext');
        $path->suffix('-new');
        $this->assertEquals('/dir/base-new.ext', $path->name());
    }

    public function test_within()
    {
        $a = new Path('/parent');
        $b = new Path('/parent/child');

        $this->assertTrue($b->isWithin($a));
        $this->assertFalse($a->isWithin($b));
    }

    public function test_absolute()
    {
        $a = new Path('../four');
        $b = new Path('/one/two/three');

        $this->assertEquals('/one/four', $a->toAbsolute($b)->name());
    }

    public function test_absolute_exception()
    {
        $a = new Path('../four');
        $b = new Path('/one/two/three');

        $this->expectException('Exception');
        $b->toAbsolute($a);
    }

    public function test_relative()
    {
        $a = new Path('/one/four');
        $b = new Path('/one/two/three');

        $this->assertEquals('../four', $a->toRelative($b)->name());
    }

    public function test_relative_exception()
    {
        $a = new Path('/one/four');
        $b = new Path('../');

        $this->expectException('Exception');
        $a->toRelative($b);
    }

    public function test_type()
    {
        $abs = new Path('/name');
        $rel = new Path('name');

        $this->assertTrue($abs->isAbsolute());
        $this->assertTrue($rel->isRelative());
    }

    public function test_real()
    {
        $path = new Path(__DIR__.'/..');
        $real = $path->toReal();

        $this->assertEquals($real->name(), realpath(__DIR__.'/..'));

        $path = new Path('./');
        $this->expectException('Exception');
        $path->toReal();
    }

    public function test_child()
    {
        $path = new Path('/one/two/three');

        $this->assertEquals('/one/two/three/four', $path->child('four')->name());
    }

    public function test_parent()
    {
        $path = new Path('/one/two/three');

        $this->assertEquals('/one/two', $path->parent()->name());
    }

    public function test_dir()
    {
        $path = new Path('/one/two/three');
        $dir = $path->toDir();

        $this->assertInstanceOf('Coast\Dir', $dir);
        $this->assertEquals('/one/two/three', $dir->name());
    }

    public function test_file()
    {
        $path = new Path('/one/two/three');
        $file = $path->toFile();

        $this->assertInstanceOf('Coast\File', $file);
        $this->assertEquals('/one/two/three', $file->name());
    }

    public function test_parts()
    {
        $path = new Path('/dir/base.ext');
        $this->assertEquals(['', 'dir', 'base.ext'], $path->parts());
    }

    public function test_part()
    {
        $path = new Path('/dir/base.ext');
        $this->assertEquals('base.ext', $path->part(2));
        $this->assertEquals('base.ext', $path->part(0, true));
    }
}
