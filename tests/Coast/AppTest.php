<?php

namespace Coast\Test;

use Coast\App;
use Coast\Request;
use Coast\Resolver;
use Coast\Response;
use Coast\Url;

class AppTest extends \PHPUnit\Framework\TestCase
{
    public function test_env()
    {
        $app = new App(__DIR__, ['DEBUG' => true]);
        $this->assertTrue($app->env('DEBUG'));
    }

    public function test_mode()
    {
        $app = new App(__DIR__);
        $this->assertEquals(App::MODE_CLI, $app->mode());
        $this->assertTrue($app->isCli());
        $this->assertFalse($app->isHttp());
    }

    public function test_execute()
    {
        $app = new App(__DIR__);
        $app->executable(function (Request $req, Response $res) {
            return $res->text('OK');
        });
        $app->executable(function (Request $req, Response $res) {
            return $res->text('OK');
        });
        $app->execute($req = new Request, $res = new Response($req));
        $this->assertEquals('OK', $res->body());
    }

    public function test_execute_exception()
    {
        $app = new App(__DIR__);
        $this->expectException('Coast\App\Exception');
        $app->execute(new Request);
    }

    public function test_invalid()
    {
        $app = new App(__DIR__);
        $this->expectException('Coast\App\Exception');
        $app->add(true);
    }

    public function test_failure_handler()
    {
        $app = new App(__DIR__);
        $app->failureHandler(function (Request $req, Response $res) {
            return $res->text('NOT FOUND');
        });
        $app->execute($req = new Request, $res = new Response($req));
        $this->assertEquals('NOT FOUND', $res->body());
    }

    public function test_error_handler()
    {
        $app = new App(__DIR__);
        $app->errorHandler(function (Request $req, Response $res) {
            return $res->text('ERROR');
        });
        $app->execute($req = new Request, $res = new Response($req));
        $this->assertEquals('ERROR', $res->body());
    }

    public function test_param()
    {
        $app = new App(__DIR__);
        $app->param('test', 'OK');
        $this->assertEquals('OK', $app->param('test'));

        $app = new App(__DIR__);
        $app->test = 'OK';
        $this->assertTrue(isset($app->test));
        $this->assertEquals('OK', $app->test);
    }

    public function test_params()
    {
        $app = new App(__DIR__);
        $params = [
            'test1' => 'OK',
            'test2' => 'OK',
            'app' => $app,
        ];
        $app->params($params);
        $this->assertEquals($params, $app->params());
    }

    public function test_access()
    {
        $app = new App(__DIR__);
        $app->param('url', new Resolver([
            'baseUrl' => new Url('/'),
        ]));
        $this->assertEquals('/test', $app->url('test')->toString());
    }

    public function test_access_exception()
    {
        $app = new App(__DIR__);
        $app->param('invalid', true);
        $this->expectException('Coast\App\Exception');
        $app->invalid();
    }
}
