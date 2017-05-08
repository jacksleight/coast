<?php
/*
 * Copyright 2017 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\Validator\Rule;

use Coast\Validator\Rule;
use Coast\Http;
use Coast\Url;

class Recaptcha extends Rule
{
	const CONNECT = 'connect';
	const INVALID = 'invalid';

    protected $_config;

    protected static $_configStatic;

    public static function configStatic(array $config = null)
    {
        if (func_num_args() > 0) {
            self::$_configStatic = $config;
        }
        return self::$_configStatic;
    }

	public function __construct(array $config = null)
	{
        if (isset($config)) {
            $this->config($config);
        }
	}

    public function config(array $config = null)
    {
        if (func_num_args() > 0) {
            $this->_config = $config;
            return $this;
        }
        return $this->_config;
    }

	protected function _validate($value)
	{
        $config = isset($this->_config)
            ? $this->_config
            : self::$_configStatic;
            
        $http = new Http();
        $req = new Http\Request([
            'url' => (new Url('https://www.google.com/recaptcha/api/siteverify'))->queryParams([
                'secret'   => isset($config['secretKey'])
                    ? $config['secretKey']
                    : null,
                'response' => $value,
                'remoteip' => isset($config['remoteIp'])
                    ? $config['remoteIp']
                    : null,
            ]),
        ]);
        $res = $http->execute($req);
		if (!$res->isSuccess()) {
			$this->error(self::CONNECT);
		}
		$data = $res->json(); 
		if (!isset($data->success) || $data->success == false) {
			$this->error(self::INVALID);
		}	
	}
}