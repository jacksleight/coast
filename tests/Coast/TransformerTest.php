<?php
namespace Coast\Test;

use Coast\Transformer,
    Coast\Transformer\Rule;
use DateTime;

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
        $transformer = new Rule\Boolean([true, 1], ['0', 'false']);
        $this->assertTrue($transformer(true));
        $this->assertTrue($transformer(1));
        $this->assertFalse($transformer('0'));
        $this->assertFalse($transformer('false'));
        $this->assertEquals($transformer('test'), 'test');

        $this->assertEquals($transformer->true(), [true, 1]);
        $this->assertEquals($transformer->false(), ['0', 'false']);
    }

    public function testDateTime()
    {
        $date = new DateTime('now');
        $transformer = new Rule\DateTime('Y-m-d', 'Europe/London');
        $this->assertEquals($transformer($date), $date);
        $this->assertEquals($transformer($date->format('Y-m-d')), $date);
        $this->assertEquals($transformer($date->format('Y-m')), $date->format('Y-m'));

        $this->assertEquals($transformer->format(), 'Y-m-d');
        $this->assertEquals($transformer->timezone(), 'Europe/London');
        
        $date = new DateTime('now');
        $transformer = new Rule\DateTime(null, 'Europe/London');
        $this->assertEquals($transformer('now'), $date);
        $this->assertEquals($transformer('test'), 'test');
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
        $rule = $transformer->rule('null');
        $this->assertEquals($rule[0]->name(), 'null');
        $rules = $transformer->rules();
        $this->assertEquals($rules['null'][0]->name(), 'null');
    }

    public function testClone()
    {
        $transformer1 = (new Transformer())
            ->null(); 
        $transformer2 = clone $transformer1;
        $rules1 = $transformer1->rule('null');
        $rules2 = $transformer2->rule('null');
        $this->assertNotSame($rules1[0], $rules2[0]);
    }

    public function testName()
    {
        $transformer = new Rule\Null();
        $transformer->name('test');
        $this->assertEquals($transformer->name(), 'test');
    }
}