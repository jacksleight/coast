<?php

namespace Coast\Test;

use Coast\Filter;
use Coast\Filter\Rule;

class FilterTest extends \PHPUnit\Framework\TestCase
{
    public function test_camel_case()
    {
        $filter = new Rule\CamelCase;
        $this->assertEquals($filter('one two'), 'oneTwo');

        $this->assertEquals($filter->mode(), Rule\CamelCase::MODE_LOWER);
    }

    public function test_camel_case_split()
    {
        $filter = new Rule\CamelCaseSplit;
        $this->assertEquals($filter('oneTwo'), 'one Two');

        $this->assertEquals($filter->space(), ' ');
    }

    public function test_encode_special_chars()
    {
        $filter = new Rule\EncodeSpecialChars;
        $this->assertEquals($filter('&'), '&#38;');
    }

    public function test_lower_case()
    {
        $filter = new Rule\LowerCase;
        $this->assertEquals($filter('TEST'), 'test');
    }

    public function test_upper_case()
    {
        $filter = new Rule\UpperCase;
        $this->assertEquals($filter('test'), 'TEST');
    }

    public function test_decimal()
    {
        $filter = new Rule\DecimalType;
        $this->assertEquals($filter('1.5t'), '1.5');
    }

    public function test_email_address()
    {
        $filter = new Rule\EmailAddress;
        $this->assertEquals($filter('test@ example.com'), 'test@example.com');
    }

    public function test_integer()
    {
        $filter = new Rule\IntegerType;
        $this->assertEquals($filter('1.t'), '1');
    }

    public function test_float()
    {
        $filter = new Rule\FloatType;
        $this->assertEquals($filter('1.5t'), '1.5');
    }

    public function test_number()
    {
        $filter = new Rule\NumberType;
        $this->assertEquals($filter('1.5t'), '1.5');
    }

    public function test_url()
    {
        $filter = new Rule\Url;
        $this->assertEquals($filter('http://example .com/'), 'http://example.com/');
    }

    public function test_slugify()
    {
        $filter = new Rule\Slugify;
        $this->assertEquals($filter('one two'), 'one-two');

        $this->assertEquals($filter->space(), '-');
    }

    public function test_title_case()
    {
        $filter = new Rule\TitleCase;
        $this->assertEquals($filter('one two'), 'One Two');
    }

    public function test_strip_tags()
    {
        $filter = new Rule\StripTags('<i>');
        $this->assertEquals($filter('<i>one</i> <b>two</b>'), '<i>one</i> two');

        $this->assertEquals($filter->allowed(), '<i>');
    }

    public function test_trim()
    {
        $filter = new Rule\Trim(' ', Rule\Trim::MODE_BOTH);
        $this->assertEquals($filter(' one '), 'one');

        $this->assertEquals($filter->chars(), ' ');
        $this->assertEquals($filter->mode(), Rule\Trim::MODE_BOTH);
    }

    public function test_custom()
    {
        $func = function ($value) {
            return strtoupper($value);
        };
        $filter = new Rule\Custom($func);
        $this->assertEquals($filter('test'), 'TEST');

        $this->assertEquals($filter->func(), $func);
    }

    public function test_break()
    {
        $filter = (new Filter)
            ->break()
            ->upperCase();
        $this->assertSame($filter(null), null);
    }

    public function test_steps()
    {
        $filter1 = (new Filter)
            ->trim();
        $filter2 = (new Filter)
            ->steps($filter1->steps());
        $this->assertEquals($filter1('  test  '), 'test');
        $this->assertEquals($filter2('  test  '), 'test');
    }

    public function test_rules()
    {
        $filter = (new Filter)
            ->trim();
        $rule = $filter->rule('trim');
        $this->assertEquals($rule[0]->name(), 'trim');
        $rules = $filter->rules();
        $this->assertEquals($rules['trim'][0]->name(), 'trim');
    }

    public function test_clone()
    {
        $filter1 = (new Filter)
            ->trim();
        $filter2 = clone $filter1;
        $rules1 = $filter1->rule('trim');
        $rules2 = $filter2->rule('trim');
        $this->assertNotSame($rules1[0], $rules2[0]);
    }

    public function test_name()
    {
        $filter = new Rule\Trim;
        $filter->name('test');
        $this->assertEquals($filter->name(), 'test');
    }
}
