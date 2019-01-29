<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
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

    protected $_config = [];

    protected static $_configStatic = [];

    public static function configStatic(array $configStatic = null)
    {
        if (func_num_args() > 0) {
            self::$_configStatic = $configStatic;
        }
        return self::$_configStatic;
    }

	public function __construct(array $config = null)
	{
        $this->config(isset($config) ? $config : self::$_configStatic);
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
        $http = new Http();
        $req = new Http\Request([
            'url' => (new Url('https://www.google.com/recaptcha/api/siteverify'))->queryParams([
                'secret'   => isset($this->_config['secretKey'])
                    ? $this->_config['secretKey']
                    : null,
                'response' => $value,
                'remoteip' => isset($this->_config['remoteIp'])
                    ? $this->_config['remoteIp']
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