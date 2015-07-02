<?php
/*
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Path;
use Coast\File;
use Coast\Dir;
use Coast\View\Content;

class View implements \Coast\App\Access, \Coast\App\Executable
{
    use \Coast\App\Access\Implementation;

    protected $_dirs = [];

    protected $_extName = 'php';

    protected $_stack = [];

    protected $_current;

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
            if (!$name) {
                $name = 'default';
            }
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

    public function render($name, array $params = array(), $group = null, Content $previous = null)
    {  
        $path = new \Coast\Path("{$name}.{$this->_extName}");
        if (isset($this->_current)) {
            if (!isset($group)) {
                $path = $path->isRelative()
                    ? $path->toAbsolute($this->_current->sets[0]->path)
                    : $path;
                $group = $this->_current->sets[0]->group;
            } else if (!$path->isAbsolute()) {
                $path = new \Coast\Path("/{$path}");
            }
        } else {
            if (!$path->isAbsolute()) {
                $path = new \Coast\Path("/{$path}");
            }
            if (!isset($group)) {
                $group = 'default';
            }
        }
        if (!isset($this->_dirs[$group])) {
            throw new View\Exception("View group '{$group}' does not exist");
        }

        $sets = [(object) [
            'name'  => $name,
            'path'  => $path,
            'group' => $group,
        ]];

        



        do {
            $set = &$sets[0];
            $set->obj = $this->_dirs[$group]->path($path);
            $set->obj = $set->obj->isDir()
                ? $set->obj->toDir()
                : $set->obj->toFile();


            if ($set->obj instanceof Dir) {
                $file = $set->obj->file('extends');
                if ($file->exists()) {


                    $file->open('r');
                    $extends = explode(',', $file->read());
                    $file->close();

                    $name  = $extends[0];
                    $group = isset($extends[1])
                        ? $extends[1]
                        : null;


                    $path = new \Coast\Path("{$name}.{$this->_extName}");
                    if (!isset($group)) {
                        $path = $path->isRelative()
                            ? $path->toAbsolute($set->path)
                            : $path;
                        $group = $set->group;
                    } else if (!$path->isAbsolute()) {
                        $path = new \Coast\Path("/{$path}");
                    }
                    if (!isset($this->_dirs[$group])) {
                        throw new View\Exception("View group '{$group}' does not exist");
                    }

                    array_unshift($sets, (object) [
                        'name'  => $name,
                        'path'  => $path,
                        'group' => $group,
                    ]);

                }
            }
 

            



            


        } while (!isset($sets[0]->obj));

 

        // tidy up the above junky code
        // figure out way to re-use path resolving
        // add a part->parent feature




        array_unshift($this->_stack, (object) [
            'sets'     => array_reverse($sets),
            'params'   => $params,
            'parent'   => null,
            'block'    => null,
            'content'  => new Content(),
            'previous' => $previous,
            'captures' => 0,
        ]);
        $this->_current = &$this->_stack[0];

        $content = $this->part();

        $this->_current->content->block($this->_current->block, $content);
        $this->_current->block = null;
        while ($this->_current->captures > 0) {
            $this->end();
        }

        $content = $this->_current->content;
        if (isset($this->_current->parent)) {
            list($name, $params, $group) = $this->_current->parent;
            $content = $this->render($name, $params, $group, $content);           
        }

        array_shift($this->_stack);
        if (count($this->_stack)) {
            $this->_current = &$this->_stack[0];
        } else {
            $this->_current = null;
        }

        return $content;
    }
       
    public function part($part = 'index')
    {
        foreach ($this->_current->sets as $set) {
            $file = $set->obj instanceof Dir
                ? $set->obj->file("{$part}.{$this->_extName}")
                : $set->obj;
            if ($file->exists()) {
                break;
            }
        }
        if (!$file->exists()) {
            throw new View\Exception("View '{$set->group}:{$set->name}" . (isset($part) ? ":{$part}" : null) . "' does not exist");
        }
        return $this->run($file, $this->_current->params);
    }
        
    public function run(File $__file, array $__params = array())
    {
        $this->start();
        try {
            extract($__params);
            include (string) $__file;
        } catch (\Exception $e) {
            while ($this->_current->captures > 0) {
                echo $this->end();
            }
            throw $e;
        }
        return $this->end();        
    }
        
    public function child($name, array $params = array(), $group = null)
    {
        if (!isset($this->_current)) {
            throw new View\Exception("Cannot call child() before render()");
        }

        $params = array_merge($this->_current->params, $params);
        return $this->render($name, $params, $group);      
    }

    protected function parent($name, array $params = array(), $group = null)
    {
        if (!isset($this->_current)) {
            throw new View\Exception("Cannot call parent() before render()");
        }
        
        $params = array_merge($this->_current->params, $params);
        $this->_current->parent = [$name, $params, $group, false];
    }

    protected function block($name = null)
    {
        $content = $this->end();
        if (strlen($content) > 0 ) {
            $this->_current->content->block($this->_current->block, $content);
        }
        $name = isset($name)
            ? $name
            : $this->_current->content->next();
        $this->_current->block = $name;
        $this->start();

        return true;
    }

    protected function content($name = null)
    {
        if (!isset($this->_current->previous)) {
            return;
        }
        return isset($name)
            ? $this->_current->previous->block($name)
            : $this->_current->previous;
    }
    
    protected function start()
    {
        ob_start();
        $this->_current->captures++;
    }
    
    protected function end()
    {
        $this->_current->captures--;
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
        try {
            return $res->html($this->render($path, [
                'req' => $req,
                'res' => $res,
            ]));
        } catch (View\Exception $e) {
            return false;
        }
    }
} 
