<?php

namespace Coast\Test;

use Coast\DateTime;
use Coast\Transformer;
use Coast\Transformer\Rule;

class TransformerTest extends \PHPUnit\Framework\TestCase
{
    public function test_null()
    {
        $transformer = new Rule\NullType;
        $this->assertEquals($transformer(''), null);
        $this->assertEquals($transformer('test'), 'test');
        $this->assertEquals($transformer([]), []);
    }

    public function test_url()
    {
        $transformer = new Rule\Url;
        $this->assertEquals($transformer('http://example.com/'), new \Coast\Url('http://example.com/'));
        $this->assertEquals($transformer([]), []);
    }

    public function test_boolean()
    {
        $transformer = new Rule\BooleanType;

        $this->assertTrue($transformer(true));
        $this->assertTrue($transformer(1));
        $this->assertTrue($transformer('1'));
        $this->assertTrue($transformer('true'));
        $this->assertTrue($transformer('on'));
        $this->assertTrue($transformer('yes'));
        $this->assertTrue($transformer('test'));

        $this->assertFalse($transformer(false));
        $this->assertFalse($transformer(0));
        $this->assertFalse($transformer('0'));
        $this->assertFalse($transformer('false'));
        $this->assertFalse($transformer('off'));
        $this->assertFalse($transformer('no'));
        $this->assertFalse($transformer(''));

        $transformer = new Rule\BooleanType([true, 1], ['0', 'false']);
        $this->assertEquals($transformer->true(), [true, 1]);
        $this->assertEquals($transformer->false(), ['0', 'false']);
    }

    public function test_integer()
    {
        $transformer = new Rule\IntegerType;
        $this->assertEquals($transformer('1'), 1);
        $this->assertEquals($transformer('1.4'), 1);
    }

    public function test_date_time()
    {
        $date = new DateTime('today');
        $transformer = new Rule\DateTime('Y-m-d', 'Europe/London');
        $this->assertEquals($transformer($date)->format('Y-m-d'), $date->format('Y-m-d'));
        $this->assertEquals($transformer((array) $date), $date);
        $this->assertEquals($transformer([]), []);

        $this->assertEquals($transformer->format(), 'Y-m-d');
        $this->assertEquals($transformer->timezone(), 'Europe/London');

        $date = new DateTime('today');
        $transformer = new Rule\DateTime(null, 'Europe/London');
        $this->assertEquals($transformer('today'), $date);
        $this->assertEquals($transformer('test'), 'test');
    }

    public function test_array()
    {
        $transformer = new Rule\ArrayType;
        $this->assertEquals($transformer('1,2,3'), ['1', '2', '3']);
        $this->assertEquals($transformer(['1', '2', '3']), ['1', '2', '3']);
    }

    public function test_custom()
    {
        $func = function ($value) {
            return [$value];
        };
        $transformer = new Rule\Custom($func);
        $this->assertEquals($transformer('test'), ['test']);

        $this->assertEquals($transformer->func(), $func);
    }

    public function test_break()
    {
        $transformer = (new Transformer)
            ->break()
            ->boolean();
        $this->assertSame($transformer(null), null);
    }

    public function test_steps()
    {
        $transformer1 = (new Transformer)
            ->null();
        $transformer2 = (new Transformer)
            ->steps($transformer1->steps());
        $this->assertEquals($transformer1(''), null);
        $this->assertEquals($transformer2(''), null);
    }

    public function test_rules()
    {
        $transformer = (new Transformer)
            ->null();
        $rule = $transformer->rule('nullType');
        $this->assertEquals($rule[0]->name(), 'nullType');
        $rules = $transformer->rules();
        $this->assertEquals($rules['nullType'][0]->name(), 'nullType');
    }

    public function test_clone()
    {
        $transformer1 = (new Transformer)
            ->null();
        $transformer2 = clone $transformer1;
        $rules1 = $transformer1->rule('nullType');
        $rules2 = $transformer2->rule('nullType');
        $this->assertNotSame($rules1[0], $rules2[0]);
    }

    public function test_name()
    {
        $transformer = new Rule\NullType;
        $transformer->name('test');
        $this->assertEquals($transformer->name(), 'test');
    }
}
