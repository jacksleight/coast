<?php
namespace Coast\Test;

use Coast\Url;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    public function testString()
    {
        $value = 'http://user:pass@host:8080/path?name=value#fragment';
        $url = new Url($value);
        $this->assertEquals($value, (string) $url);
        $this->assertEquals($value, $url->toString());
        $this->assertEquals('http', $url->scheme());
        $this->assertEquals('user', $url->user());
        $this->assertEquals('pass', $url->pass());
        $this->assertEquals('host', $url->host());
        $this->assertEquals('8080', $url->port());
        $this->assertEquals('/path', $url->path());
        $this->assertEquals('name=value', $url->query());
        $this->assertEquals('fragment', $url->fragment());
        $this->assertEquals('http://user:pass@host:8080', $url->toPart(Url::PART_PORT)->toString());

        $value = 'http://user@host/';
        $url = new Url($value);
        $this->assertEquals($value, $url->toString());

        $value = '//host/';
        $url = new Url($value);
        $this->assertEquals($value, $url->toString());

        $value = '?test=1';
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

    public function testAbsolute()
    {
        $url1 = new Url('http://host/example/one');
        $url2 = new Url('../two');
        $this->assertTrue($url2->toAbsolute($url1)->toString() == 'http://host/two');

        $this->setExpectedException('Exception');
        $url1->toAbsolute($url1);
    }

    public function testRelative()
    {
        $url1 = new Url('http://host/example/one');
        $url2 = new Url('http://host/host/two');
        $this->assertTrue($url1->toRelative($url2)->toString() == '../example/one');

        $url3 = new Url('../two');
        $this->setExpectedException('Exception');
        $url3->toRelative($url3);
    }
}