<?php
namespace Coast\Test;

use Coast\Url;

class UrlTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals('http://username:password@host:8080', $url->toPart(Url::PART_PORT)->toString());

        $value = 'http://username@host/';
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
        $this->assertEquals(['test' => 'OK'], $url->queryParams());

        $url->queryParam('test', null);
        $this->assertEquals([], $url->queryParams());
    }

    public function testQueryParams()
    {
        $url = new Url();
        $url->queryParams([
            'test1' => 'OK',
            'test2' => 'OK',
        ]);
        $this->assertEquals([
            'test1' => 'OK',
            'test2' => 'OK',
        ], $url->queryParams());

        $url->queryParams(null);
        $this->assertEquals([], $url->queryParams());
    } 

    public function testQuery()
    {
        $url = new Url();
        $url->query('test1=OK&test2=OK');
        $this->assertEquals('test1=OK&test2=OK', $url->query());

        $url->query(null);
        $this->assertEquals(null, $url->query());
    } 

    public function testAbsolute()
    {
        $url1 = new Url('http://host/example/one');
        $url2 = new Url('../two');
        $this->assertTrue($url2->toAbsolute($url1)->toString() == 'http://host/two');

        $this->expectException('Exception');
        $url1->toAbsolute($url1);
    }

    public function testRelative()
    {
        $url1 = new Url('http://host/example/one');
        $url2 = new Url('http://host/host/two');
        $this->assertTrue($url1->toRelative($url2)->toString() == '../example/one');

        $url3 = new Url('../two');
        $this->expectException('Exception');
        $url3->toRelative($url3);
    }

    public function testBase()
    {
        $value = 'http://host/';
        $url = new Url($value);
        
        $this->assertEquals('host', $url->host());
        $this->assertEquals('host', $url->host);
        $this->assertEquals('host', $url['host']);

        $url->path('/a');
        $this->assertEquals('/a', $url->path());
        $url->path = '/b';
        $this->assertEquals('/b', $url->path());
        $url['path'] = '/c';
        $this->assertEquals('/c', $url->path());

        $this->expectException('Error');
        $url->fake();
        $url->fake;
        $url['fake'];
    }
}