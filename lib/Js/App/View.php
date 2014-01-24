<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Js\App;

class View implements \Js\App\Access, \Js\App\Executable
{
	use \Js\App\Access\Implementation;
	use \Js\Options;

	protected $_stack = array();

	public function __construct(array $options = array())
	{
		$this->setOptions(array_merge([
			'dir'		=> null,
			'extension'	=> 'phtml',
		], $options));
	}

	protected function _initOption($name, $value)
	{
		switch ($name) {
			case 'dir':
				$value = new \Js\Dir("{$value}");
				break;
		}
		return $value;
	}
		
	public function has($name)
	{
		$path = new \Js\Path("{$name}." . $this->_options->extension);
		if ($path->isRelative()) {
			throw new \Js\App\Exception("Initial path '{$path}' is relative");
		}

		$file = $this->_options->dir->getFile($path);	
		return $file->isFile();
	}
		
	public function render($name, array $params = array())
	{
		$path = new \Js\Path("{$name}." . $this->_options->extension);
		if (count($this->_stack) > 0) {
			$path = $path->isRelative()
				? $this->_stack[0]['path']->fromRelative($path)
				: $path;
			$params	= array_merge($this->_stack[0]['params'], $params);
		} else if ($path->isRelative()) {
			throw new \Js\App\Exception("Initial path '{$path}' is relative");
		}

		$file = $this->_options->dir->getFile($path);	
		if (!$file->isFile()) {
			if (count($this->_stack) == 0) {
				throw new \Js\App\Exception("View file '{$path}' does not exist");
			} else {
				throw new \Js\App\Exception("View file '{$path}' does not exist");				
			}
		}

		array_unshift($this->_stack, array(
			'name'		=> $name, 
			'path'		=> $path, 
			'params'	=> $params, 
			'layout'	=> null, 
			'block'		=> null, 
			'content'	=> new \Js\App\View\Content(), 
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
			include $_file->toString();
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

	public function execute(\Js\App\Request $req, \Js\App\Response $res)
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