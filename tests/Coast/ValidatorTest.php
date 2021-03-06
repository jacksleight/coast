<?php
namespace Coast\Test;

use Coast\Validator,
    Coast\Validator\Rule;
use DateTime;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testBoolean()
    {
        $validator = new Rule\BooleanType([true, 1], ['0', 'false']);
        $this->assertTrue($validator(true));
        $this->assertTrue($validator(1));
        $this->assertTrue($validator('0'));
        $this->assertFalse($validator('text'));

        $this->assertEquals($validator->true(), [true, 1]);
        $this->assertEquals($validator->false(), ['0', 'false']);
    }

    public function testCount()
    {
        $validator = new Rule\Count(1, 2);
        $this->assertTrue($validator([0]));
        $this->assertFalse($validator([]));
        $this->assertFalse($validator([0, 1, 2]));

        $this->assertEquals($validator->min(), 1);
        $this->assertEquals($validator->max(), 2);
    }

    public function testLength()
    {
        $validator = new Rule\Length(1, 2);
        $this->assertTrue($validator('0'));
        $this->assertFalse($validator(''));
        $this->assertFalse($validator('012'));

        $this->assertEquals($validator->min(), 1);
        $this->assertEquals($validator->max(), 2);
    }

    public function testRange()
    {
        $validator = new Rule\Range(1, 2);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(0));
        $this->assertFalse($validator(3));

        $this->assertEquals($validator->min(), 1);
        $this->assertEquals($validator->max(), 2);
    }

    public function testFile()
    {
        $validator = new Rule\File(1000, ['txt'], true, true);
        $this->assertEquals($validator->size(), 1000);
        $this->assertEquals($validator->types(), ['txt']);
        $this->assertEquals($validator->readable(), true);
        $this->assertEquals($validator->writable(), true);

        $validator = new Rule\File();
        $this->assertFalse($validator('invalid'));
        $this->assertFalse($validator(new \Coast\File('invalid.txt')));

        $validator = new Rule\File(1);
        $this->assertFalse($validator(new \Coast\File(__FILE__)));

        $validator = new Rule\File(null, ['txt']);
        $this->assertFalse($validator(new \Coast\File(__FILE__)));

        $validator = new Rule\File(null, null, true);
        $this->assertFalse($validator(new \Coast\File(__FILE__)));

        $validator = new Rule\File(null, null, null, true);
        $this->assertFalse($validator(new \Coast\File(__FILE__)));
    }

    public function testUpload()
    {
        $validator = new Rule\Upload(1000, ['txt']);
        $this->assertTrue($validator([
            'name'     => 'test.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error'    => UPLOAD_ERR_OK,
            'size'     => 100,
        ]));
        $this->assertFalse($validator([
            'name'     => 'test.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => '/tmp/php/test',
            'error'    => UPLOAD_ERR_OK,
            'size'     => 100,
        ]));
        $this->assertFalse($validator([
            'name'     => 'test.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error'    => UPLOAD_ERR_INI_SIZE,
            'size'     => 100,
        ]));
        $this->assertFalse($validator([
            'name'     => 'test.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error'    => UPLOAD_ERR_NO_FILE,
            'size'     => 100,
        ]));
        $this->assertFalse($validator([
            'name'     => 'test.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error'    => UPLOAD_ERR_NO_TMP_DIR,
            'size'     => 100,
        ]));
        $this->assertFalse($validator([
            'name'     => 'test.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error'    => UPLOAD_ERR_CANT_WRITE,
            'size'     => 100,
        ]));
        $this->assertFalse($validator([
            'name'     => 'test.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error'    => UPLOAD_ERR_EXTENSION,
            'size'     => 100,
        ]));
        $this->assertFalse($validator([
            'name'     => 'test.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error'    => UPLOAD_ERR_OK,
            'size'     => 1100,
        ]));
        $this->assertFalse($validator([
            'name'     => 'test.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'size'     => 100,
        ]));
        $this->assertFalse($validator('invalid'));

        $this->assertEquals($validator->size(), 1000);
        $this->assertEquals($validator->types(), ['txt']);
    }

    public function testDateTime()
    {
        $validator = new Rule\DateTime('Y-m-d');
        $this->assertTrue($validator('2015-01-01'));
        $this->assertFalse($validator('2015-01-'));
        $this->assertFalse($validator([]));

        $this->assertEquals($validator->format(), 'Y-m-d');

        $validator = new Rule\DateTime();
        $this->assertTrue($validator('now'));
        $this->assertFalse($validator('test'));
    }

    public function testEmailAddress()
    {
        $validator = new Rule\EmailAddress();
        $this->assertTrue($validator('test@example.com'));
        $this->assertFalse($validator('test.example.com'));
    }

    public function testEquals()
    {
        $validator = new Rule\Equals(1);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(2));

        $this->assertEquals($validator->value(), 1);
    }

    public function testMin()
    {
        $validator = new Rule\Min(10);
        $this->assertTrue($validator(11));
        $this->assertTrue($validator(10));
        $this->assertFalse($validator(9));

        $this->assertEquals($validator->value(), 10);

        $validator = new Rule\Min(new DateTime('today'));
        $this->assertTrue($validator(new DateTime('tomorrow')));
        $this->assertFalse($validator(new DateTime('yesterday')));
    }

    public function testMax()
    {
        $validator = new Rule\Max(10);
        $this->assertTrue($validator(0));
        $this->assertTrue($validator(10));
        $this->assertFalse($validator(11));

        $this->assertEquals($validator->value(), 10);

        $validator = new Rule\Max(new DateTime('today'));
        $this->assertTrue($validator(new DateTime('yesterday')));
        $this->assertFalse($validator(new DateTime('tomorrow')));
    }

    public function testFloat()
    {
        $validator = new Rule\FloatType();
        $this->assertTrue($validator(1.5));
        $this->assertFalse($validator('text'));
    }

    public function testNumber()
    {
        $validator = new Rule\NumberType();
        $this->assertTrue($validator(1));
        $this->assertTrue($validator(1.5));
        $this->assertFalse($validator('text'));
    }

    public function testDecimal()
    {
        $validator = new Rule\DecimalType();
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
        $validator = new Rule\IntegerType();
        $this->assertTrue($validator(1));
        $this->assertFalse($validator('text'));
    }

    public function testArr()
    {
        $validator = new Rule\ArrayType();
        $this->assertTrue($validator([]));
        $this->assertFalse($validator('text'));
    }

    public function testObject()
    {
        $obj1 = new \DateTime();
        $obj2 = new \DateTimezone('UTC');

        $validator = new Rule\ObjectType();
        $this->assertTrue($validator($obj1));
        $this->assertFalse($validator('text'));

        $validator = new Rule\ObjectType('DateTime');
        $this->assertTrue($validator($obj1));
        $this->assertFalse($validator($obj2));

        $this->assertEquals($validator->className(), 'DateTime');
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

        $this->assertEquals($validator->lower(), 2);
        $this->assertEquals($validator->upper(), 2);
        $this->assertEquals($validator->digit(), 2);
        $this->assertEquals($validator->special(), 2);
    }

    public function testString()
    {
        $validator = new Rule\StringType();
        $this->assertTrue($validator('text'));
        $this->assertFalse($validator([]));
    }

    public function testRegex()
    {
        $validator = new Rule\Regex('/[0-9]/');
        $this->assertTrue($validator('a0b'));
        $this->assertFalse($validator('ab'));

        $this->assertEquals($validator->regex(), '/[0-9]/');
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

        $this->assertEquals($validator->func(), 'is_object');
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

    public function testIn()
    {
        $validator = new Rule\In([0, 1, 2]);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(4));

        $this->assertEquals($validator->values(), [0, 1, 2]);
    }

    public function testMap()
    {
        $validator = (new Validator())
            ->array();
        $rules = $validator->rules();
        $this->assertEquals($rules['array'][0]->name(), 'array');
    }

    public function testCustom()
    {
        $func = function($value, $rule) {
            if ($value != 1) {
                $rule->error(null, [
                    'test' => 1,
                ]);
            };
        };
        $validator = new Rule\Custom($func);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(2));

        $this->assertEquals($validator->func(), $func);
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
        $this->assertFalse($validator(1));
    }

    public function testIsValid()
    {
        $validator = (new Validator())
            ->set();
        $validator(null);
        $this->assertFalse($validator->isValid());
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

    public function testRules()
    {
        $validator = (new Validator())
            ->set();
        $rule = $validator->rule('set');
        $this->assertEquals($rule[0]->name(), 'set');
        $rules = $validator->rules();
        $this->assertEquals($rules['set'][0]->name(), 'set');
    }

    public function testClone()
    {
        $validator1 = (new Validator())
            ->set(); 
        $validator2 = clone $validator1;
        $rules1 = $validator1->rule('set');
        $rules2 = $validator2->rule('set');
        $this->assertNotSame($rules1[0], $rules2[0]);
    }

    public function testName()
    {
        $validator = new Rule\Set();
        $validator->name('test');
        $this->assertEquals($validator->name(), 'test');
    }

    public function testParams()
    {
        $validator = new Rule\Count(1, 2);
        $this->assertEquals($validator->params(), [
            'min' => 1,
            'max' => 2,
        ]);
    }
}