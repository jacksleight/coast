<?php
/*
 * Copyright 2014 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast\App;

class View implements \Coast\App\Access, \Coast\App\Executable
{
    use \Coast\App\Access\Implementation;

    protected $_baseDirs = [];

    protected $_extName = 'php';

    protected $_stack = [];

    public function __construct($baseDirs = array())
    {
        if (!is_array($baseDirs)) {
            $baseDirs = [$baseDirs];
        }
        $this->baseDirs($baseDirs);
    }

    public function baseDir($name, \Coast\Dir $baseDir = null)
    {
        if (isset($baseDir)) {
            $this->_baseDirs[$name] = $baseDir;
            return $this;
        }
        return isset($this->_baseDirs[$name])
            ? $this->_baseDirs[$name]
            : null;;
    }

    public function baseDirs(array $baseDirs = null)
    {
        if (isset($baseDirs)) {
            foreach ($baseDirs as $name => $baseDir) {
                $this->baseDir($name, $baseDir);
            }
            return $this;
        }
        return $this->_baseDirs;
    }

    public function extName($extName = null)
    {
        if (isset($extName)) {
            $this->_extName = $extName;
            return $this;
        }
        return $this->_extName;
    }
        
    public function has($name, $set = null)
    {
        if (!isset($set)) {
            reset($this->_baseDirs);
            $set = key($this->baseDirs);
        }
        $path = new \Coast\Path("{$name}." . $this->_extName);
        if (!$path->isAbsolute()) {
            $path = new \Coast\Path("/{$path}");
        }
        if (!isset($this->_baseDirs[$set])) {
            return false;
        }
        $file = $this->_baseDirs[$set]->file($path);    
        return $file->exists();
    }
        
    public function render($name, array $params = array(), $set = null)
    {
        $path = new \Coast\Path("{$name}." . $this->_extName);
        if (count($this->_stack) > 0) {
            if (!isset($set)) {
                $path = $path->isRelative()
                    ? $path->toAbsolute($this->_stack[0]['path'])
                    : $path;
                $set = $this->_stack[0]['set'];
            } else if (!$path->isAbsolute()) {
                $path = new \Coast\Path("/{$path}");
            }
            $params = array_merge($this->_stack[0]['params'], $params);
        } else {
            if (!$path->isAbsolute()) {
                $path = new \Coast\Path("/{$path}");
            }
            if (!isset($set)) {
                reset($this->_baseDirs);
                $set = key($this->_baseDirs);
            }
        }
        if (!isset($this->_baseDirs[$set])) {
            throw new \Coast\App\Exception("View set '{$set}' does not exist");
        }
        $file = $this->_baseDirs[$set]->file($path);    
        if (!$file->exists()) {
            throw new \Coast\App\Exception("View file '{$set}:{$path}' does not exist");
        }

        array_unshift($this->_stack, [
            'name'     => $name, 
            'path'     => $path, 
            'params'   => $params, 
            'set'      => $set,
            'layout'   => null, 
            'block'    => null, 
            'content'  => new \Coast\App\View\Content(), 
            'captures' => 0,
        ]);
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
        $this->_stack[0]['layout'] = [$name, $params, $set];
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

    /**
     * Move these
     */
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
        $path = $req->path();
        $path = '/' . (strlen($path) ? $path : 'index');
        if (!$this->has($path)) {
            return false;
        }
        return $res->html($this->render($path, [
            'req' => $req,
            'res' => $res,
        ]));
    }
} 
