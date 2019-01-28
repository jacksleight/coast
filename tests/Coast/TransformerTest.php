<?php
namespace Coast\Test;

use Coast\Transformer,
    Coast\Transformer\Rule;
use DateTime;

class TransformerTest extends \PHPUnit\Framework\TestCase
{
    public function testNull()
    {
        $transformer = new Rule\NullType();
        $this->assertEquals($transformer(''), null);
        $this->assertEquals($transformer('test'), 'test');
        $this->assertEquals($transformer([]), []);
    }

    public function testUrl()
    {
        $transformer = new Rule\Url();
        $this->assertEquals($transformer('http://example.com/'), new \Coast\Url('http://example.com/'));
        $this->assertEquals($transformer([]), []);
    }

    public function testBoolean()
    {
        $transformer = new Rule\BooleanType();

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

    public function testInteger()
    {
        $transformer = new Rule\IntegerType();
        $this->assertEquals($transformer('1'), 1);
        $this->assertEquals($transformer('1.4'), 1);
    }

    public function testDateTime()
    {
        $date = new DateTime('today');
        $transformer = new Rule\DateTime('Y-m-d', 'Europe/London');
        $this->assertEquals($transformer($date)->format('Y-m-d'), $date->format('Y-m-d'));
        $this->assertEquals($transformer($date->format('Y-m')), $date->format('Y-m'));
        $this->assertEquals($transformer((array) $date), $date);
        $this->assertEquals($transformer([]), []);

        $this->assertEquals($transformer->format(), 'Y-m-d');
        $this->assertEquals($transformer->timezone(), 'Europe/London');

        $date = new DateTime('today');
        $transformer = new Rule\DateTime(null, 'Europe/London');
        $this->assertEquals($transformer('today'), $date);
        $this->assertEquals($transformer('test'), 'test');
    }

    public function testArray()
    {
        $transformer = new Rule\ArrayType();
        $this->assertEquals($transformer('1,2,3'), ['1', '2', '3']);
        $this->assertEquals($transformer(['1', '2', '3']), ['1', '2', '3']);
    }

    public function testCustom()
    {
        $func = function($value) {
            return [$value];
        };
        $transformer = new Rule\Custom($func);
        $this->assertEquals($transformer('test'), ['test']);

        $this->assertEquals($transformer->func(), $func);
    }

    public function testBreak()
    {
        $transformer = (new Transformer())
            ->break()
            ->boolean();
        $this->assertSame($transformer(null), null);
    }

    public function testSteps()
    {
        $transformer1 = (new Transformer())
            ->null();
        $transformer2 = (new Transformer())
            ->steps($transformer1->steps());
        $this->assertEquals($transformer1(''), null);
        $this->assertEquals($transformer2(''), null);
    }

    public function testRules()
    {
        $transformer = (new Transformer())
            ->null();
        $rule = $transformer->rule('nullType');
        $this->assertEquals($rule[0]->name(), 'nullType');
        $rules = $transformer->rules();
        $this->assertEquals($rules['nullType'][0]->name(), 'nullType');
    }

    public function testClone()
    {
        $transformer1 = (new Transformer())
            ->null();
        $transformer2 = clone $transformer1;
        $rules1 = $transformer1->rule('nullType');
        $rules2 = $transformer2->rule('nullType');
        $this->assertNotSame($rules1[0], $rules2[0]);
    }

    public function testName()
    {
        $transformer = new Rule\NullType();
        $transformer->name('test');
        $this->assertEquals($transformer->name(), 'test');
    }
}