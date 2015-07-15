<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\App\Access;
use Coast\App\Executable;
use Coast\Request;
use Coast\Response;
use Coast\Session;

class Csrf implements Executable, Access
{
    use Access\Implementation;
    use Executable\Implementation;

    protected $_name = 'csrf';

    protected $_session = null;

    protected $_methods = [
        Request::METHOD_PUT,
        Request::METHOD_POST,
        Request::METHOD_DELETE,
    ];

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function name($name = null)
    {
        if (func_num_args() > 0) {
            $this->_name = $name;
            return $this;
        }
        return $this->_name;
    }

    public function session(Session $session = null)
    {
        if (func_num_args() > 0) {
            $this->_session = $session;
            return $this;
        }
        return $this->_session;
    }

    public function methods(array $methods = null)
    {
        if (func_num_args() > 0) {
            $this->_methods = $methods;
            return $this;
        }
        return $this->_methods;
    }

    public function token()
    {
        $data = $this->_session->data('__Coast\Csrf');
        if (!isset($data->token)) {
            $data->token = \Coast\str_random();
        }
        return $data->token;
    }

    public function regenerate()
    {
        $data = $this->_session->data('__Coast\Csrf');
        $data->token = \Coast\str_random();
        return $this;
    }

    public function input()
    {
        return "<input type=\"hidden\" name=\"{$this->_name}\" value=\"{$this->token()}\">";
    }

    public function execute(Request $req, Response $res)
    {
        if (!in_array($req->method(), $this->_methods)) {
            return;
        }

        $data = $this->_session->data('__Coast\Csrf');
        if (!isset($data->token)) {
            throw new Csrf\Exception('CSRF token not generated');
        } else if ($req->param($this->_name) === null) {
            throw new Csrf\Exception('CSRF token not provided');
        } else if ($req->param($this->_name) !== $data->token) {
            throw new Csrf\Exception('CSRF token invalid');                
        }

        $req->param($this->_name, null);
    }
}