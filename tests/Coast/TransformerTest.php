<?php
namespace Coast\Test;

use Coast\Transformer,
    Coast\Transformer\Rule;

class TransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testNull()
    {
        $transformer = new Rule\Null();
        $this->assertEquals($transformer(''), null);
        $this->assertEquals($transformer('test'), 'test');
        $this->assertEquals($transformer(1), 1);
    }

    public function testBoolean()
    {
        $validator = new Rule\Boolean([true, 1], ['0', 'false']);
        $this->assertTrue($validator(true));
        $this->assertTrue($validator(1));
        $this->assertFalse($validator('0'));
        $this->assertFalse($validator('false'));
        $this->assertEquals($validator('test'), 'test');

        $this->assertEquals($validator->true(), [true, 1]);
        $this->assertEquals($validator->false(), ['0', 'false']);
    }

    public function testCustom()
    {
        $func = function($value) {
            return [$value];
        };
        $filter = new Rule\Custom($func);
        $this->assertEquals($filter('test'), ['test']);

        $this->assertEquals($filter->func(), $func);
    }
}