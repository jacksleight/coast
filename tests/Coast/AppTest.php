<?php
namespace Coast;

use Coast\App, 
    Coast\App\Request,
    Coast\App\Response,
    Coast\App\Url;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testEnv()
    {
        $app = new App(['DEBUG' => true]);
        $this->assertTrue($app->env('DEBUG'));
    }

    public function testMode()
    {
        $app = new App();
        $this->assertEquals(App::MODE_CLI, $app->mode());
        $this->assertTrue($app->isCli());
        $this->assertFalse($app->isHttp());
    }

    public function testExecute()
    {
        $app = new App();
        $app->add(function(Request $req, Response $res) {
            return $res->text('OK');
        });
        $app->add('test', function(Request $req, Response $res) {
            return $res->text('OK');
        });
        $res = $app->execute(new Request());
        $this->assertEquals('OK', $res->body());
    }

    public function testExecuteException()
    {
        $app = new App();
        $this->setExpectedException('Coast\App\Exception');
        $app->execute(new Request());
    }

    public function testInvalid()
    {
        $app = new App();
        $this->setExpectedException('Coast\App\Exception');
        $app->add(true);
    }

    public function testNotFoundHandler()
    {
        $app = new App();
        $app->notFoundHandler(function(Request $req, Response $res) {
            return $res->text('NOT FOUND');
        });
        $res = $app->execute(new Request());
        $this->assertEquals('NOT FOUND', $res->body());
    }

    public function testErrorHandler()
    {
        $app = new App();
        $app->errorHandler(function(Request $req, Response $res) {
            return $res->text('ERROR');
        });
        $res = $app->execute(new Request());
        $this->assertEquals('ERROR', $res->body());
    }

    public function testParam()
    {
        $app = new App();
        $app->set('test', 'OK');
        $this->assertTrue($app->has('test'));
        $this->assertEquals('OK', $app->get('test'));

        $app = new App();
        $app->test = 'OK';
        $this->assertTrue(isset($app->test));
        $this->assertEquals('OK', $app->test);
    }

    public function testParams()
    {
        $app = new App();
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
        $app = new App();
        $app->set('url', new Url());
        $this->assertEquals('/test', $app->url('test')->toString());
    }

    public function testAccessException()
    {
        $app = new App();
        $app->set('invalid', true);
        $this->setExpectedException('Coast\App\Exception');
        $app->invalid();
    }
}