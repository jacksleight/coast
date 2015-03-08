<?php
namespace Coast\Test;

use Coast\App, 
    Coast\Request,
    Coast\Response,
    Coast\App\UrlResolver,
    Coast\Url;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testEnv()
    {
        $app = new App(__DIR__, ['DEBUG' => true]);
        $this->assertTrue($app->env('DEBUG'));
    }

    public function testMode()
    {
        $app = new App(__DIR__);
        $this->assertEquals(App::MODE_CLI, $app->mode());
        $this->assertTrue($app->isCli());
        $this->assertFalse($app->isHttp());
    }

    public function testExecute()
    {
        $app = new App(__DIR__);
        $app->executable(function(Request $req, Response $res) {
            return $res->text('OK');
        });
        $app->executable(function(Request $req, Response $res) {
            return $res->text('OK');
        });
        $app->execute($req = new Request(), $res = new Response($req));
        $this->assertEquals('OK', $res->body());
    }

    public function testExecuteException()
    {
        $app = new App(__DIR__);
        $this->setExpectedException('Coast\App\Exception');
        $app->execute(new Request());
    }

    public function testInvalid()
    {
        $app = new App(__DIR__);
        $this->setExpectedException('Coast\App\Exception');
        $app->add(true);
    }

    public function testNotFoundHandler()
    {
        $app = new App(__DIR__);
        $app->notFoundHandler(function(Request $req, Response $res) {
            return $res->text('NOT FOUND');
        });
        $app->execute($req = new Request(), $res = new Response($req));
        $this->assertEquals('NOT FOUND', $res->body());
    }

    public function testErrorHandler()
    {
        $app = new App(__DIR__);
        $app->errorHandler(function(Request $req, Response $res) {
            return $res->text('ERROR');
        });
        $app->execute($req = new Request(), $res = new Response($req));
        $this->assertEquals('ERROR', $res->body());
    }

    public function testParam()
    {
        $app = new App(__DIR__);
        $app->param('test', 'OK');
        $this->assertEquals('OK', $app->param('test'));

        $app = new App(__DIR__);
        $app->test = 'OK';
        $this->assertTrue(isset($app->test));
        $this->assertEquals('OK', $app->test);
    }

    public function testParams()
    {
        $app = new App(__DIR__);
        $params = [
            'test1' => 'OK',
            'test2' => 'OK',
            'app'   => $app,
        ];
        $app->params($params);
        $this->assertEquals($params, $app->params());
    }

    public function testAccess()
    {
        $app = new App(__DIR__);
        $app->param('url', new UrlResolver([
            'baseUrl' => new Url('/'),
        ]));
        $this->assertEquals('/test', $app->url('test')->toString());
    }

    public function testAccessException()
    {
        $app = new App(__DIR__);
        $app->param('invalid', true);
        $this->setExpectedException('Coast\App\Exception');
        $app->invalid();
    }
}