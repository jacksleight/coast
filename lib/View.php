<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\View\Content;

class View implements \Coast\App\Access, \Coast\App\Executable
{
    use \Coast\App\Access\Implementation;

    protected $_dirs = [];

    protected $_extName = 'php';

    protected $_stack = [];

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function dir($name, \Coast\Dir $dir = null)
    {
        if (func_num_args() > 0) {
            $this->_dirs[$name] = $dir;
            return $this;
        }
        return isset($this->_dirs[$name])
            ? $this->_dirs[$name]
            : null;
    }

    public function dirs(array $dirs = null)
    {
        if (func_num_args() > 0) {
            foreach ($dirs as $name => $dir) {
                $this->dir($name, $dir);
            }
            return $this;
        }
        return $this->_dirs;
    }

    public function extName($extName = null)
    {
        if (func_num_args() > 0) {
            $this->_extName = $extName;
            return $this;
        }
        return $this->_extName;
    }
                
    public function valid($name, $set = null)
    {
        if (!isset($set)) {
            reset($this->_dirs);
            $set = key($this->_dirs);
        }
        $path = new \Coast\Path("{$name}." . $this->_extName);
        if (!$path->isAbsolute()) {
            $path = new \Coast\Path("/{$path}");
        }
        if (!isset($this->_dirs[$set])) {
            return false;
        }
        $file = $this->_dirs[$set]->file($path);
        return $file->exists();
    }
        
    public function render($name, array $params = array(), $set = null, Content $previous = null, $extend = false)
    {
        $path = new \Coast\Path("{$name}." . $this->_extName);
        if (count($this->_stack)) {
            if (!isset($set)) {
                $path = $path->isRelative()
                    ? $path->toAbsolute($this->_stack[0]['path'])
                    : $path;
                $set = $this->_stack[0]['set'];
            } else if (!$path->isAbsolute()) {
                $path = new \Coast\Path("/{$path}");
            }
        } else {
            if (!$path->isAbsolute()) {
                $path = new \Coast\Path("/{$path}");
            }
            if (!isset($set)) {
                reset($this->_dirs);
                $set = key($this->_dirs);
            }
        }
        if (!isset($this->_dirs[$set])) {
            throw new View\Exception("View set '{$set}' does not exist");
        }
        $file = $this->_dirs[$set]->file($path);    
        if (!$file->exists()) {
            throw new View\Exception("View file '{$set}:{$path}' does not exist");
        }

        array_unshift($this->_stack, [
            'name'     => $name, 
            'path'     => $path, 
            'params'   => $params, 
            'set'      => $set,
            'parent'   => null, 
            'block'    => null, 
            'content'  => new Content(), 
            'previous' => $previous, 
            'extend'   => $extend, 
            'captures' => 0,
        ]);
        $this->_run($file, $params);
        $content = $this->_stack[0]['content'];
        if (isset($this->_stack[0]['parent'])) {
            list($name, $params, $set, $extend) = $this->_stack[0]['parent'];
            $content = $this->render($name, $params, $set, $content, $extend);           
        }
        array_shift($this->_stack);

        return $content;
    }
        
    protected function _run($_file, array $_params = array())
    {
        $this->start();
        try {
            extract($_params);
            include (string) $_file;
        } catch (\Exception $e) {
            while ($this->_stack[0]['captures'] > 0) {
                echo $this->end();
            }
            throw $e;
        }
        
        $content = trim($this->end());
        if (strlen($content) > 0 ) {
            $this->_stack[0]['content']->block($this->_stack[0]['block'], $content);
        }
        $this->_stack[0]['block'] = null;
        while ($this->_stack[0]['captures'] > 0) {
            $this->end();
        }
    }
        
    public function child($name, array $params = array(), $set = null)
    {
        if (!count($this->_stack)) {
            throw new View\Exception("Cannot call child() before render()");
        }

        $params = array_merge($this->_stack[0]['params'], $params);
        return $this->render($name, $params, $set);      
    }

    protected function parent($name, array $params = array(), $set = null)
    {
        if (!count($this->_stack)) {
            throw new View\Exception("Cannot call parent() before render()");
        }
        
        $params = array_merge($this->_stack[0]['params'], $params);
        $this->_stack[0]['parent'] = [$name, $params, $set, false];
    }

    protected function extend($name, array $params = array(), $set = null)
    {
        if (!count($this->_stack)) {
            throw new View\Exception("Cannot call extend() before render()");
        }
        
        $params = array_merge($this->_stack[0]['params'], $params);
        $this->_stack[0]['parent'] = [$name, $params, $set, true];
    }

    protected function block($name = null)
    {
        $content = trim($this->end());
        if (strlen($content) > 0 ) {
            $this->_stack[0]['content']->block($this->_stack[0]['block'], $content);
        }
        $name = isset($name)
            ? $name
            : $this->_stack[0]['content']->next();
        $this->_stack[0]['block'] = $name;
        $this->start();

        if ($this->_stack[0]['extend'] && isset($this->_stack[0]['previous']->{$name})) {
            $this->_stack[0]['content']->block($name, $this->_stack[0]['previous']->{$name});
            return false;
        }
        return true;
    }

    protected function content($name = null)
    {
        if (!isset($this->_stack[0]['previous'])) {
            return;
        }
        return isset($name)
            ? $this->_stack[0]['previous']->block($name)
            : $this->_stack[0]['previous'];
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

    public function execute(\Coast\Request $req, \Coast\Response $res)
    {        
        $path = $req->path();
        $path = '/' . (strlen($path) ? $path : 'index');
        if (!$this->valid($path)) {
            return false;
        }
        return $res->html($this->render($path, [
            'req' => $req,
            'res' => $res,
        ]));
    }
} 
