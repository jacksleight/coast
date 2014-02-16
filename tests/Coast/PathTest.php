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

        $this->assertTrue($b->within($a));
        $this->assertFalse($a->within($b));
    }

    public function testResolve()
    {
        $a = new Path('../four');
        $b = new Path('/one/two/three');

        $this->assertEquals('/one/four', $a->resolve($b)->name());

        $this->setExpectedException('Exception');
        $this->assertEquals('/one/four', $b->resolve($a)->name());
    }

    public function testUnresolve()
    {
        $a = new Path('/one/four');
        $b = new Path('/one/two/three');
        $c = new Path('../');

        $this->assertEquals('../../one/four', $a->unresolve($b)->name());
      
        $this->setExpectedException('Exception');
        $this->assertEquals('../../one/four', $a->unresolve($c)->name());
    }

    public function testType()
    {
        $abs = new Path('/name');
        $rel = new Path('name');

        $this->assertTrue($abs->absolute());
        $this->assertTrue($rel->relative());
    }
}