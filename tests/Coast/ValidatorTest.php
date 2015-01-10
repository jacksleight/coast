<?php
namespace Coast;

use Coast\Validator,
    Coast\Validator\Rule;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testBoolean()
    {
        $validator = new Rule\Boolean();
        $this->assertTrue($validator(true));
        $this->assertTrue($validator(1));
        $this->assertTrue($validator('0'));
        $this->assertFalse($validator('text'));
    }

    public function testCount()
    {
        $validator = new Rule\Count(1, 2);
        $this->assertTrue($validator([0]));
        $this->assertFalse($validator([]));
        $this->assertFalse($validator([0, 1, 2]));
    }

    public function testLength()
    {
        $validator = new Rule\Length(1, 2);
        $this->assertTrue($validator('0'));
        $this->assertFalse($validator(''));
        $this->assertFalse($validator('012'));
    }

    public function testRange()
    {
        $validator = new Rule\Range(1, 2);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(0));
        $this->assertFalse($validator(3));
    }

    public function testDateTime()
    {
        $validator = new Rule\DateTime('Y-m-d');
        $this->assertTrue($validator('2015-01-01'));
        $this->assertFalse($validator('2015-01-40'));
    }

    public function testEmailAddress()
    {
        $validator = new Rule\EmailAddress();
        $this->assertTrue($validator('test@example.com'));
        $this->assertFalse($validator('test.example.com'));
    }

    public function testValue()
    {
        $validator = new Rule\Value(1);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(2));
    }

    public function testFloat()
    {
        $validator = new Rule\Float();
        $this->assertTrue($validator(1.5));
        $this->assertFalse($validator('text'));
    }

    public function testNumber()
    {
        $validator = new Rule\Number();
        $this->assertTrue($validator(1));
        $this->assertTrue($validator(1.5));
        $this->assertFalse($validator('text'));
    }

    public function testHostname()
    {
        $validator = new Rule\Hostname();
        $this->assertTrue($validator('example.com'));
        $this->assertFalse($validator('http://example.com'));
    }

    public function testInteger()
    {
        $validator = new Rule\Integer();
        $this->assertTrue($validator(1));
        $this->assertFalse($validator('text'));
    }

    public function testArr()
    {
        $validator = new Rule\Arr();
        $this->assertTrue($validator([]));
        $this->assertFalse($validator('text'));
    }

    public function testIpAddress()
    {
        $validator = new Rule\IpAddress();
        $this->assertTrue($validator('127.0.0.1'));
        $this->assertFalse($validator('example.com'));
    }

    public function testPassword()
    {
        $validator = new Rule\Password(2, 2, 2, 2);
        $this->assertTrue($validator('aaBB00??'));
        $this->assertFalse($validator('aB0?'));
    }

    public function testString()
    {
        $validator = new Rule\String();
        $this->assertTrue($validator('text'));
        $this->assertFalse($validator([]));
    }

    public function testRegex()
    {
        $validator = new Rule\Regex('/[0-9]/');
        $this->assertTrue($validator('a0b'));
        $this->assertFalse($validator('ab'));
    }

    public function testFunc()
    {
        $validator = new Rule\Func(function($value) {
            return $value == 123;
        });
        $this->assertTrue($validator(123));
        $this->assertFalse($validator(321));

        $validator = new Rule\Func('is_object');
        $this->assertTrue($validator(new \stdClass()));
        $this->assertFalse($validator('text'));
    }

    public function testSet()
    {
        $validator = new Rule\Set();
        $this->assertTrue($validator('text'));
        $this->assertFalse($validator(null));
    }

    public function testUrl()
    {
        $validator = new Rule\Url();
        $this->assertTrue($validator('http://example.com/'));
        $this->assertFalse($validator('example.com'));
    }

    public function testValues()
    {
        $validator = new Rule\Values([0, 1, 2]);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(4));
    }

    public function testErrors()
    {
        $validator = (new Validator())
            ->set()
            ->number()
            ->range(10, 20);
        $this->assertFalse($validator(5));
        $this->assertEquals($validator->errors(), array(
            0 => array(
                0 => 'range',
                1 => 'min',
                2 => array(
                    'min' => 10,
                    'max' => 20,
                ),
            ),
        ));
    }

    public function testBreak()
    {
        $validator = (new Validator())
            ->set()
            ->break()
            ->number()
            ->range(10, 20);
        $this->assertFalse($validator(null));
        $this->assertEquals($validator->errors(), array(
            0 => array(
                0 => 'set',
                1 => null,
                2 => array(),
            ),
        ));
    }

    public function testFalse()
    {
        $validator = (new Validator())
            ->notSet();
        $this->assertTrue($validator(null));
    }

    public function testSteps()
    {
        $validator1 = (new Validator())
            ->set();
        $validator2 = (new Validator())
            ->steps($validator1->steps());
        $this->assertFalse($validator1(null));
        $this->assertFalse($validator2(null));
    }
}