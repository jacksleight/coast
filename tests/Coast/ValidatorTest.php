<?php

namespace Coast\Test;

use Coast\Validator;
use Coast\Validator\Rule;
use DateTime;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function test_boolean()
    {
        $validator = new Rule\BooleanType([true, 1], ['0', 'false']);
        $this->assertTrue($validator(true));
        $this->assertTrue($validator(1));
        $this->assertTrue($validator('0'));
        $this->assertFalse($validator('text'));

        $this->assertEquals($validator->true(), [true, 1]);
        $this->assertEquals($validator->false(), ['0', 'false']);
    }

    public function test_count()
    {
        $validator = new Rule\Count(1, 2);
        $this->assertTrue($validator([0]));
        $this->assertFalse($validator([]));
        $this->assertFalse($validator([0, 1, 2]));

        $this->assertEquals($validator->min(), 1);
        $this->assertEquals($validator->max(), 2);
    }

    public function test_length()
    {
        $validator = new Rule\Length(1, 2);
        $this->assertTrue($validator('0'));
        $this->assertFalse($validator(''));
        $this->assertFalse($validator('012'));

        $this->assertEquals($validator->min(), 1);
        $this->assertEquals($validator->max(), 2);
    }

    public function test_range()
    {
        $validator = new Rule\Range(1, 2);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(0));
        $this->assertFalse($validator(3));

        $this->assertEquals($validator->min(), 1);
        $this->assertEquals($validator->max(), 2);
    }

    public function test_file()
    {
        $validator = new Rule\File(1000, ['txt'], true, true);
        $this->assertEquals($validator->size(), 1000);
        $this->assertEquals($validator->types(), ['txt']);
        $this->assertEquals($validator->readable(), true);
        $this->assertEquals($validator->writable(), true);

        $validator = new Rule\File;
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

    public function test_upload()
    {
        $validator = new Rule\Upload(1000, ['txt']);
        $this->assertTrue($validator([
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error' => UPLOAD_ERR_OK,
            'size' => 100,
        ]));
        $this->assertFalse($validator([
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/php/test',
            'error' => UPLOAD_ERR_OK,
            'size' => 100,
        ]));
        $this->assertFalse($validator([
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error' => UPLOAD_ERR_INI_SIZE,
            'size' => 100,
        ]));
        $this->assertFalse($validator([
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error' => UPLOAD_ERR_NO_FILE,
            'size' => 100,
        ]));
        $this->assertFalse($validator([
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error' => UPLOAD_ERR_NO_TMP_DIR,
            'size' => 100,
        ]));
        $this->assertFalse($validator([
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error' => UPLOAD_ERR_CANT_WRITE,
            'size' => 100,
        ]));
        $this->assertFalse($validator([
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error' => UPLOAD_ERR_EXTENSION,
            'size' => 100,
        ]));
        $this->assertFalse($validator([
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'error' => UPLOAD_ERR_OK,
            'size' => 1100,
        ]));
        $this->assertFalse($validator([
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/php/test',
            'size' => 100,
        ]));
        $this->assertFalse($validator('invalid'));

        $this->assertEquals($validator->size(), 1000);
        $this->assertEquals($validator->types(), ['txt']);
    }

    public function test_date_time()
    {
        $validator = new Rule\DateTime('Y-m-d');
        $this->assertTrue($validator('2015-01-01'));
        $this->assertFalse($validator('2015-01-'));
        $this->assertFalse($validator([]));

        $this->assertEquals($validator->format(), 'Y-m-d');

        $validator = new Rule\DateTime;
        $this->assertTrue($validator('now'));
        $this->assertFalse($validator('test'));
    }

    public function test_email_address()
    {
        $validator = new Rule\EmailAddress;
        $this->assertTrue($validator('test@example.com'));
        $this->assertFalse($validator('test.example.com'));
    }

    public function test_equals()
    {
        $validator = new Rule\Equals(1);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(2));

        $this->assertEquals($validator->value(), 1);
    }

    public function test_min()
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

    public function test_max()
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

    public function test_float()
    {
        $validator = new Rule\FloatType;
        $this->assertTrue($validator(1.5));
        $this->assertFalse($validator('text'));
    }

    public function test_number()
    {
        $validator = new Rule\NumberType;
        $this->assertTrue($validator(1));
        $this->assertTrue($validator(1.5));
        $this->assertFalse($validator('text'));
    }

    public function test_decimal()
    {
        $validator = new Rule\DecimalType;
        $this->assertTrue($validator(1.5));
        $this->assertFalse($validator('text'));
    }

    public function test_hostname()
    {
        $validator = new Rule\Hostname;
        $this->assertTrue($validator('example.com'));
        $this->assertFalse($validator('http://example.com'));
    }

    public function test_integer()
    {
        $validator = new Rule\IntegerType;
        $this->assertTrue($validator(1));
        $this->assertFalse($validator('text'));
    }

    public function test_arr()
    {
        $validator = new Rule\ArrayType;
        $this->assertTrue($validator([]));
        $this->assertFalse($validator('text'));
    }

    public function test_object()
    {
        $obj1 = new \DateTime;
        $obj2 = new \DateTimezone('UTC');

        $validator = new Rule\ObjectType;
        $this->assertTrue($validator($obj1));
        $this->assertFalse($validator('text'));

        $validator = new Rule\ObjectType('DateTime');
        $this->assertTrue($validator($obj1));
        $this->assertFalse($validator($obj2));

        $this->assertEquals($validator->className(), 'DateTime');
    }

    public function test_ip_address()
    {
        $validator = new Rule\IpAddress;
        $this->assertTrue($validator('127.0.0.1'));
        $this->assertFalse($validator('example.com'));
    }

    public function test_password()
    {
        $validator = new Rule\Password(2, 2, 2, 2);
        $this->assertTrue($validator('aaBB00??'));
        $this->assertFalse($validator('aB0?'));

        $this->assertEquals($validator->lower(), 2);
        $this->assertEquals($validator->upper(), 2);
        $this->assertEquals($validator->digit(), 2);
        $this->assertEquals($validator->special(), 2);
    }

    public function test_string()
    {
        $validator = new Rule\StringType;
        $this->assertTrue($validator('text'));
        $this->assertFalse($validator([]));
    }

    public function test_regex()
    {
        $validator = new Rule\Regex('/[0-9]/');
        $this->assertTrue($validator('a0b'));
        $this->assertFalse($validator('ab'));

        $this->assertEquals($validator->regex(), '/[0-9]/');
    }

    public function test_func()
    {
        $validator = new Rule\Func(function ($value) {
            return $value == 123;
        });
        $this->assertTrue($validator(123));
        $this->assertFalse($validator(321));

        $validator = new Rule\Func('is_object');
        $this->assertTrue($validator(new \stdClass));
        $this->assertFalse($validator('text'));

        $this->assertEquals($validator->func(), 'is_object');
    }

    public function test_set()
    {
        $validator = new Rule\Set;
        $this->assertTrue($validator('text'));
        $this->assertFalse($validator(null));
    }

    public function test_url()
    {
        $validator = new Rule\Url;
        $this->assertTrue($validator('http://example.com/'));
        $this->assertFalse($validator('example.com'));
    }

    public function test_in()
    {
        $validator = new Rule\In([0, 1, 2]);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(4));

        $this->assertEquals($validator->values(), [0, 1, 2]);
    }

    public function test_map()
    {
        $validator = (new Validator)
            ->array();
        $rules = $validator->rules();
        $this->assertEquals($rules['array'][0]->name(), 'array');
    }

    public function test_custom()
    {
        $func = function ($value, $rule) {
            if ($value != 1) {
                $rule->error(null, [
                    'test' => 1,
                ]);
            }
        };
        $validator = new Rule\Custom($func);
        $this->assertTrue($validator(1));
        $this->assertFalse($validator(2));

        $this->assertEquals($validator->func(), $func);
    }

    public function test_errors()
    {
        $validator = (new Validator)
            ->set()
            ->number()
            ->range(10, 20);
        $this->assertFalse($validator(5));
        $this->assertEquals($validator->errors(), [
            0 => [
                0 => 'range',
                1 => 'min',
                2 => [
                    'min' => 10,
                    'max' => 20,
                ],
            ],
        ]);
    }

    public function test_break()
    {
        $validator = (new Validator)
            ->set()
            ->break()
            ->number()
            ->range(10, 20);
        $this->assertFalse($validator(null));
        $this->assertEquals($validator->errors(), [
            0 => [
                0 => 'set',
                1 => null,
                2 => [],
            ],
        ]);
    }

    public function test_false()
    {
        $validator = (new Validator)
            ->notSet();
        $this->assertTrue($validator(null));
        $this->assertFalse($validator(1));
    }

    public function test_is_valid()
    {
        $validator = (new Validator)
            ->set();
        $validator(null);
        $this->assertFalse($validator->isValid());
    }

    public function test_steps()
    {
        $validator1 = (new Validator)
            ->set();
        $validator2 = (new Validator)
            ->steps($validator1->steps());
        $this->assertFalse($validator1(null));
        $this->assertFalse($validator2(null));
    }

    public function test_rules()
    {
        $validator = (new Validator)
            ->set();
        $rule = $validator->rule('set');
        $this->assertEquals($rule[0]->name(), 'set');
        $rules = $validator->rules();
        $this->assertEquals($rules['set'][0]->name(), 'set');
    }

    public function test_clone()
    {
        $validator1 = (new Validator)
            ->set();
        $validator2 = clone $validator1;
        $rules1 = $validator1->rule('set');
        $rules2 = $validator2->rule('set');
        $this->assertNotSame($rules1[0], $rules2[0]);
    }

    public function test_name()
    {
        $validator = new Rule\Set;
        $validator->name('test');
        $this->assertEquals($validator->name(), 'test');
    }

    public function test_params()
    {
        $validator = new Rule\Count(1, 2);
        $this->assertEquals($validator->params(), [
            'min' => 1,
            'max' => 2,
        ]);
    }
}
