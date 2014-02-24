<?php
use Coast\Path;

namespace Coast;

class PathTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $path = new Path('/dir/base.ext');

        $this->assertEquals('/dir/base.ext', (string) $path);
        $this->assertEquals('/dir/base.ext', $path->name());
        $this->assertEquals('/dir', $path->dirname());
        $this->assertEquals('base.ext', $path->basename());
        $this->assertEquals('ext', $path->extname());
        $this->assertEquals('base', $path->filename());
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

        $this->assertEquals('/one/four', $a->absolute($b)->name());

        $this->setExpectedException('Exception');
        $this->assertEquals('/one/four', $b->absolute($a)->name());
    }

    public function testRelative()
    {
        $a = new Path('/one/four');
        $b = new Path('/one/two/three');
        $c = new Path('../');

        $this->assertEquals('../four', $a->relative($b)->name());
      
        $this->setExpectedException('Exception');
        $this->assertEquals('../four', $a->relative($c)->name());
    }

    public function testType()
    {
        $abs = new Path('/name');
        $rel = new Path('name');

        $this->assertTrue($abs->isAbsolute());
        $this->assertTrue($rel->isRelative());
    }
}