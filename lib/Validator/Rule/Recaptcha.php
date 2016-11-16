<?php
/*
 * Copyright 2016 Jack Sleight <http://jacksleight.com/>
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

	protected $_secretKey = null;
	protected $_remoteIp = null;

	public function __construct($secretKey, $remoteIp = null)
	{
		$this->secretKey($secretKey);
		$this->remoteIp($remoteIp);
	}

    public function secretKey($secretKey = null)
    {
        if (func_num_args() > 0) {
            $this->_secretKey = $secretKey;
            return $this;
        }
        return $this->_secretKey;
    }

    public function remoteIp($remoteIp = null)
    {
        if (func_num_args() > 0) {
            $this->_remoteIp = $remoteIp;
            return $this;
        }
        return $this->_remoteIp;
    }

	protected function _validate($value)
	{
        $http = new Http();
        $req = new Http\Request([
            'url' => (new Url('https://www.google.com/recaptcha/api/siteverify'))->queryParams([
                'secret'   => $this->_secretKey,
                'response' => $value,
                'remoteip' => $this->_remoteIp,
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