<?php
namespace Coast\Test;

use Coast\Filter,
    Coast\Filter\Rule;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testNullify()
    {
        $filter = new Rule\Nullify();
        $this->assertEquals($filter(''), null);
    }

    public function testCamelCase()
    {
        $filter = new Rule\CamelCase();
        $this->assertEquals($filter('one two'), 'oneTwo');

        $this->assertEquals($filter->mode(), Rule\CamelCase::MODE_LOWER);
    }

    public function testCamelCaseSplit()
    {
        $filter = new Rule\CamelCaseSplit();
        $this->assertEquals($filter('oneTwo'), 'one Two');

        $this->assertEquals($filter->space(), ' ');
    }

    public function testEncodeSpecialChars()
    {
        $filter = new Rule\EncodeSpecialChars();
        $this->assertEquals($filter('&'), '&#38;');
    }

    public function testLowerCase()
    {
        $filter = new Rule\LowerCase();
        $this->assertEquals($filter('TEST'), 'test');
    }

    public function testUpperCase()
    {
        $filter = new Rule\UpperCase();
        $this->assertEquals($filter('test'), 'TEST');
    }

    public function testLowerCaseFirst()
    {
        $filter = new Rule\LowerCaseFirst();
        $this->assertEquals($filter('TEST'), 'tEST');
    }

    public function testUpperCaseFirst()
    {
        $filter = new Rule\UpperCaseFirst();
        $this->assertEquals($filter('test'), 'Test');
    }

    public function testSanitizeDecimal()
    {
        $filter = new Rule\SanitizeDecimal();
        $this->assertEquals($filter('1.5t'), '1.5');
    }

    public function testSanitizeEmailAddress()
    {
        $filter = new Rule\SanitizeEmailAddress();
        $this->assertEquals($filter('test@ example.com'), 'test@example.com');
    }

    public function testSanitizeFloat()
    {
        $filter = new Rule\SanitizeFloat();
        $this->assertEquals($filter('1.5t'), '1.5');
    }

    public function testSanitizeInteger()
    {
        $filter = new Rule\SanitizeInteger();
        $this->assertEquals($filter('1.t'), '1');
    }

    public function testSanitizeNumber()
    {
        $filter = new Rule\SanitizeNumber();
        $this->assertEquals($filter('1.5t'), '1.5');
    }

    public function testSanitizeUrl()
    {
        $filter = new Rule\SanitizeUrl();
        $this->assertEquals($filter('http://example .com/'), 'http://example.com/');
    }

    public function testSlugify()
    {
        $filter = new Rule\Slugify();
        $this->assertEquals($filter('one two'), 'one-two');

        $this->assertEquals($filter->space(), '-');
    }

    public function testTitleCase()
    {
        $filter = new Rule\TitleCase();
        $this->assertEquals($filter('one two'), 'One Two');
    }

    public function testStripTags()
    {
        $filter = new Rule\StripTags('<i>');
        $this->assertEquals($filter('<i>one</i> <b>two</b>'), '<i>one</i> two');

        $this->assertEquals($filter->allowed(), '<i>');
    }

    public function testTrim()
    {
        $filter = new Rule\Trim(' ', Rule\Trim::MODE_BOTH);
        $this->assertEquals($filter(' one '), 'one');

        $this->assertEquals($filter->chars(), ' ');
        $this->assertEquals($filter->mode(), Rule\Trim::MODE_BOTH);
    }

    public function testBreak()
    {
        $filter = (new Filter())
            ->nullify()
            ->break()
            ->trim();
        $this->assertSame($filter(''), null);
    }

    public function testSteps()
    {
        $filter1 = (new Filter())
            ->trim();
        $filter2 = (new Filter())
            ->steps($filter1->steps());
        $this->assertEquals($filter1('  test  '), 'test');
        $this->assertEquals($filter2('  test  '), 'test');
    }

    public function testRules()
    {
        $filter = (new Filter())
            ->trim();
        $rule = $filter->rule('trim');
        $this->assertEquals($rule[0]->name(), 'trim');
        $rules = $filter->rules();
        $this->assertEquals($rules['trim'][0]->name(), 'trim');
    }

    public function testClone()
    {
        $filter1 = (new Filter())
            ->trim(); 
        $filter2 = clone $filter1;
        $rules1 = $filter1->rule('trim');
        $rules2 = $filter2->rule('trim');
        $this->assertNotSame($rules1[0], $rules2[0]);
    }

    public function testName()
    {
        $filter = new Rule\Trim();
        $filter->name('test');
        $this->assertEquals($filter->name(), 'test');
    }

    public function testParams()
    {
        $filter = new Rule\CamelCase();
        $this->assertEquals($filter->params(), [
            'mode' => Rule\CamelCase::MODE_LOWER,
        ]);
    }
}