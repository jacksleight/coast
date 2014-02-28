<?php
namespace Coast;

use Coast\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    public function testString()
    {
        $value = 'http://username:password@host:8080/path?name=value#fragment';
        $url = new Url($value);
        $this->assertEquals($value, (string) $url);
        $this->assertEquals($value, $url->toString());
        $this->assertEquals('http', $url->scheme());
        $this->assertEquals('username', $url->username());
        $this->assertEquals('password', $url->password());
        $this->assertEquals('host', $url->host());
        $this->assertEquals('8080', $url->port());
        $this->assertEquals('/path', $url->path());
        $this->assertEquals('name=value', $url->query());
        $this->assertEquals('fragment', $url->fragment());
        $this->assertEquals('http://username:password@host:8080', $url->toString(Url::PART_PORT, true));

        $value = 'http://username@host/';
        $url = new Url($value);
        $this->assertEquals($value, $url->toString());

        $value = '//host/';
        $url = new Url($value);
        $this->assertEquals($value, $url->toString());
    }

    public function testHttp()
    {
        $url = new Url('http://host/');
        $this->assertTrue($url->isHttp());

        $url = new Url('https://host/');
        $this->assertTrue($url->isHttps());
    }

    public function testQueryParam()
    {
        $url = new Url();
        $url->queryParam('test', 'OK');
        $this->assertEquals('OK', $url->queryParam('test'));
    }

    public function testQueryParams()
    {
        $url = new Url();
        $params = [
            'test1' => 'OK',
            'test2' => 'OK',
        ];
        $url->queryParams($params);
        $this->assertEquals($params, $url->queryParams());
    } 
}