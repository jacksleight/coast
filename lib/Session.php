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

class Session implements Executable, Access
{
    use Access\Implementation;
    use Executable\Implementation;

    protected $_name = 'session';

    protected $_lifetime = null;

    protected $_expires = 1200;

    protected $_host = null;
    
    protected $_path = null;

    protected $_isSecure = null;

    protected $_fingerprint;

    protected $_request;

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

    public function lifetime($lifetime = null)
    {
        if (func_num_args() > 0) {
            $this->_lifetime = $lifetime;
            return $this;
        }
        return $this->_lifetime;
    }

    public function expires($expires = null)
    {
        if (func_num_args() > 0) {
            $this->_expires = $expires;
            return $this;
        }
        return $this->_expires;
    }

    public function host($host = null)
    {
        if (func_num_args() > 0) {
            $this->_host = $host;
            return $this;
        }
        return $this->_host;
    }

    public function path($path = null)
    {
        if (func_num_args() > 0) {
            $this->_path = $path;
            return $this;
        }
        return $this->_path;
    }

    public function isSecure($isSecure = null)
    {
        if (func_num_args() > 0) {
            $this->_isSecure = (bool) $isSecure;
            return $this;
        }
        return $this->_isSecure;
    }

    public function fingerprint(\Closure $fingerprint = null)
    {
        if (func_num_args() > 0) {
            $this->_fingerprint = $fingerprint->bindTo($this);
            return $this;
        }
        return $this->_fingerprint;
    }

    public function request(Request $req = null)
    {
        if (func_num_args() > 0) {
            $this->_request = $req;
            return $this;
        }
        return $this->_request;
    }

    public function configure()
    {   
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', 32);
        ini_set('session.hash_function', 'sha512');
        ini_set('session.hash_bits_per_character', 6);
        ini_set('session.use_cookies', true);
        ini_set('session.use_only_cookies', true);
        ini_set('session.use_trans_sid', false);
        ini_set('session.referer_check', false);

        $params = session_get_cookie_params();
        session_name($this->_name);
        session_set_cookie_params(
            isset($this->_lifetime) ? $this->_lifetime  : $params['lifetime'],
            isset($this->_path)     ? $this->_path      : $params['path'],
            isset($this->_host)     ? $this->_host      : $params['domain'],
            isset($this->_isSecure) ? $this->_isSecure  : $params['secure'],
            true
        );

        return $this;
    }

    public function start(Request $req = null)
    {
        session_start();

        if (isset($this->_fingerprint)) {
            $fingerprint = call_user_func($this->_fingerprint, $this->_request);
            if (isset($_SESSION['__Coast\Session']['fingerprint']) && $_SESSION['__Coast\Session']['fingerprint'] !== $fingerprint) {
                $this->reset();
            } else {
                $_SESSION['__Coast\Session']['fingerprint'] = $fingerprint;
            }
        }

        if (isset($this->_expires)) {
            if (isset($_SESSION['__Coast\Session']['expires']) && $_SESSION['__Coast\Session']['expires'] < time()) {
                $this->reset();
            } else {
                $_SESSION['__Coast\Session']['expires'] = time() + $this->_expires;
            }
        }

        return $this;
    }

    public function id()
    {
        return session_id();
    }

    public function regenerate()
    {
        session_regenerate_id(true);
        return $this;
    }

    public function destroy()
    {
        $params = session_get_cookie_params();
        setcookie(
            $this->_name,
            '',
            1,
            $params['path'],
            $params['domain'],
            $params['secure'],
            true
        );
        session_unset();
        session_destroy();
        return $this;
    }

    public function reset()
    {
        return $this
            ->destroy()
            ->start()
            ->regenerate();
    }

    public function data($name, $value = null)
    {
        if (func_num_args() > 1) {
            if (isset($value)) {
                $_SESSION[$name] = (object) $value;
            } else {
                unset($_SESSION[$name]);
            }
            return $this;
        } else if (!isset($_SESSION[$name])) {
            $_SESSION[$name] = new \stdClass;
        }
        return $_SESSION[$name];
    }

    public function preExecute(Request $req, Response $res)
    {
        $this->request($req);

        if (!isset($this->_host) && strpos($host = $req->host(), '.') !== false) {
            $this->host($host);
        }
        if (!isset($this->_path)) {
            $this->path($req->base());
        }
        if (!isset($this->_isSecure)) {
            $this->isSecure($req->isSecure());
        }

        $this->configure();
        $this->start();
    }

    public function __get($name)
    {
        return $this->data($name);
    }

    public function __set($name, $value)
    {
        return $this->data($name, $value);
    }

    public function __unset($name)
    {
        return $this->data($name, null);
    }
}