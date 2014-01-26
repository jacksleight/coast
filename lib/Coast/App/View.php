<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class View implements \Coast\App\Access, \Coast\App\Executable
{
	use \Coast\App\Access\Implementation;
	use \Coast\Options;

	protected $_stack = array();

	public function __construct(array $options = array())
	{
		$this->options(array_merge([
			'dir'		=> null,
			'extension'	=> 'phtml',
		], $options));
	}

	protected function _initialize($name, $value)
	{
		switch ($name) {
			case 'dir':
				$value = new \Coast\Dir("{$value}");
				break;
		}
		return $value;
	}
		
	public function has($name)
	{
		$path = new \Coast\Path("{$name}." . $this->_options->extension);
		if ($path->isRelative()) {
			throw new \Coast\App\Exception("Initial path '{$path}' is relative");
		}

		$file = $this->_options->dir->getFile($path);	
		return $file->isFile();
	}
		
	public function render($name, array $params = array())
	{
		$path = new \Coast\Path("{$name}." . $this->_options->extension);
		if (count($this->_stack) > 0) {
			$path = $path->isRelative()
				? $this->_stack[0]['path']->fromRelative($path)
				: $path;
			$params	= array_merge($this->_stack[0]['params'], $params);
		} else if ($path->isRelative()) {
			throw new \Coast\App\Exception("Initial path '{$path}' is relative");
		}

		$file = $this->_options->dir->getFile($path);	
		if (!$file->isFile()) {
			if (count($this->_stack) == 0) {
				throw new \Coast\App\Exception("View file '{$path}' does not exist");
			} else {
				throw new \Coast\App\Exception("View file '{$path}' does not exist");				
			}
		}

		array_unshift($this->_stack, array(
			'name'		=> $name, 
			'path'		=> $path, 
			'params'	=> $params, 
			'layout'	=> null, 
			'block'		=> null, 
			'content'	=> new \Coast\App\View\Content(), 
			'captures'	=> 0,
		));
		$this->_run($file, $params);
		$content = $this->_stack[0]['content'];
		if (isset($this->_stack[0]['layout'])) {
			$content = $this->render(
				$this->_stack[0]['layout'][0],
				array_merge(
					$this->_stack[0]['layout'][1],
					array('content' => $content)
				),
				$this->_stack[0]['layout'][2]
			);
		}
		array_shift($this->_stack);

		return $content;
	}

	protected function _run($_file, array $_params = array())
	{
		$this->start();
		try {
			extract($_params);
			include $_file->string();
		} catch (\Exception $e) {
			while ($this->_stack[0]['captures'] > 0) {
				echo $this->end();
			}
			throw $e;
		}
		
		$content = trim($this->end());
		if (strlen($content) > 0 ) {
			$this->_stack[0]['content']->add($content, $this->_stack[0]['block']);
		}
		$this->_stack[0]['block'] = null;
		while ($this->_stack[0]['captures'] > 0) {
			$this->end();
		}
	}

	protected function layout($name, array $params = array(), $set = null)
	{
		$this->_stack[0]['layout'] = array($name, $params, $set);
	}

	protected function block($name)
	{
		$content = trim($this->end());
		if (strlen($content) > 0 ) {
			$this->_stack[0]['content']->add($content, $this->_stack[0]['block']);
		}
		$this->_stack[0]['block'] = $name;
		$this->start();
	}
	
	protected function start()
	{
		ob_start();
		$this->_stack[0]['captures']++;
	}
	
	protected function end()
	{
		$this->_stack[0]['captures']--;
		return ob_get_clean();
	}

	protected function escape($string)
	{
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}

	protected function encode($string)
	{
		return mb_convert_encoding($string, 'UTF-8');
	}

	protected function strip($string)
	{
		return strip_tags($string);
	}

	public function execute(\Coast\App\Request $req, \Coast\App\Response $res)
	{		
		$path = '/' . $req->getPath();
		if (!$this->has($path)) {
			return false;
		}
		return $res->html($this->render($path, [
			'req' => $req,
			'res' => $res,
		]));
	}
} 