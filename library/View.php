<?php
/*
 * Copyright 2019 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\Path;
use Coast\File;
use Coast\Dir;
use Coast\View\Content;
use Coast\App\Access;
use Coast\App\Executable;

class View implements Access, Executable
{
    use Access\Implementation;
    use Executable\Implementation;

    protected $_dirs = [];

    protected $_extName = 'php';

    protected $_partialSeparator = '_';

    protected $_helpers = [];

    protected $_contexts = [];

    protected $_active;

    protected $_extensions = [];

    protected $_files = [];

    protected $_infector;

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function dir($group, Dir $dir = null)
    {
        if (func_num_args() > 0) {
            if (!$group) {
                $group = 'default';
            }
            $this->_dirs[$group] = $dir;
            $this->_meta($group, $dir);
            return $this;
        }
        return isset($this->_dirs[$group])
            ? $this->_dirs[$group]
            : null;
    }

    public function dirs(array $dirs = null)
    {
        if (func_num_args() > 0) {
            foreach ($dirs as $group => $dir) {
                $this->dir($group, $dir);
            }
            return $this;
        }
        return $this->_dirs;
    }

    public function extension($group, $source, $target)
    {
        array_unshift($this->_extensions, [
            $this->_dirs[$group]
                ->dir($source),
            $this->_dirs[isset($target[1]) ? $target[1] : $group]
                ->dir($target[0]),
        ]);
        return $this;
    }

    public function extensions($group = null, array $extensions = null)
    {
        if (func_num_args() > 0) {
            foreach ($extensions as $source => $target) {
                $this->extension($group, $source, $target);
            }
            return $this;
        }
        return $this->_extensions;
    }

    public function extName($extName = null)
    {
        if (func_num_args() > 0) {
            $this->_extName = $extName;
            return $this;
        }
        return $this->_extName;
    }

    public function partialSeparator($partialSeparator = null)
    {
        if (func_num_args() > 0) {
            $this->_partialSeparator = $partialSeparator;
            return $this;
        }
        return $this->_partialSeparator;
    }

    public function helper($name, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_helpers[$name] = $value;
            return $this;
        }
        return isset($this->_helpers[$name])
            ? $this->_helpers[$name]
            : null;
    }

    public function helpers(array $helpers = null)
    {
        if (func_num_args() > 0) {
            foreach ($helpers as $name => $value) {
                $this->helper($name, $value);
            }
            return $this;
        }
        return $this->_helpers;
    }

    protected function _meta($group, Dir $dir)
    {
        $meta = $dir->file('_.php');
        if (!$meta->exists()) {
            return;
        }

        $meta = include $meta->name();
        if (isset($meta['extensions'])) {
            $this->extensions($group, $meta['extensions']);
        }
    }

    public function script($path, $group = null)
    {  
        $path = new \Coast\Path($path);
        if (isset($this->_active)) {
            if (!isset($group)) {
                $path = $path->isRelative()
                    ? $path->toAbsolute($this->_active->script->path)
                    : $path;
                $group = $this->_active->script->group;
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
        return (object) [
            'key'   => "{$group}:{$path}",
            'group' => $group,
            'path'  => $path,
            'file'  => null,
        ];
    }

    public function files($script)
    {
        if (!isset($this->_files[$script->key])) {
            $files = [];
            $file = isset($script->file)
                ? $script->file
                : $this->_dirs[$script->group]->file("{$script->path}.{$this->_extName}");
            if ($file->exists()) {
                array_push($files, $file);
            }
            foreach ($this->_extensions as $extension) {
                if (!$file->isWithin($extension[0])) {
                    continue;
                }
                $file = $extension[1]->file($file->toRelative($extension[0]));
                if ($file->exists()) {
                    array_push($files, $file);
                }
            }
            $this->_files[$script->key] = $files;
        }

        return $this->_files[$script->key];
    }

    public function inflector(Closure $inflector)
    {
        $this->_inflector = $inflector->bindTo($this);
        return $this;
    }

    public function render($path, array $params = array(), $group = null, Content $previous = null)
    {  
        if ($path instanceof File) {
            $script = (object) [
                'key'   => $path->name(),
                'group' => 'default',
                'path'  => new Path('/'),
                'file'  => $path,
            ];
        } else {
            $script = $this->script($path, $group);
        }
        
        array_unshift($this->_contexts, (object) ([
            'script'   => $script,
            'params'   => $params,
            'vars'     => [],
            'outer'    => null,
            'block'    => null,
            'content'  => new Content(),
            'previous' => $previous,
            'buffers'  => 0,
            'renders'  => [],
        ]));
        $this->_active = &$this->_contexts[0];

        array_unshift($this->_active->renders, (object) [
            'script'    => $script,
            'depth'     => 0,
            'params'    => [],
            'isPartial' => false,
        ]);
        try {
            $content = $this->_render();
        } catch (\Exception $e) {
            $content = null;
        }
        array_shift($this->_active->renders);

        $this->_active->content->block(
            $this->_active->block,
            $content
        );
        $this->_active->block = null;
        while ($this->_active->buffers > 0) {
            $this->end();
        }

        $content = $this->_active->content;
        if (isset($this->_active->outer)) {
            list($path, $params, $group) = $this->_active->outer;
            $content = $this->render($path, $params, $group, $content);           
        }

        array_shift($this->_contexts);
        if (count($this->_contexts)) {
            $this->_active = &$this->_contexts[0];
        } else {
            $this->_active = null;
        }

        if (isset($e)) {
            throw $e;
        }

        return $content;
    }
                
    protected function _render()
    {
        $render = &$this->_active->renders[0];
        $script = $render->script;
        $depth  = $render->depth;
        $params = $render->params;
        $files  = $this->files($script);

        if (!count($files)) {
            if (!count($this->_active->renders)) {
                throw new View\Failure("View script '{$script->group}::{$script->path}' does not exist");
            } else if (!$render->isPartial) {
                throw new View\Failure("View script '{$script->group}::{$script->path}' does not exist");
            } else {
                return;
            }
        }
        if (!isset($files[$depth])) {
            throw new View\Exception("View script '{$script->group}::{$script->path}' parent at depth '{$depth}' does not exist");
        }
        return $this->_run($files[$depth], array_merge(
            $this->_active->params,
            $params
        ));
    }
        
    protected function _run(File $__file, array $__params = array())
    {
        $this->start();
        try {
            extract($this->_helpers);
            extract($__params);
            include (string) $__file;
        } catch (\Exception $e) {
            if (isset($this->_active)) {
                while ($this->_active->buffers > 0) {
                    echo $this->end();
                }
            }
            throw $e;
        }
        return $this->end();        
    }
        
    public function params(array $params)
    {
        $this->_active->params = array_merge(
            $this->_active->params,
            $params
        );
    }
        
    public function pass(array $params)
    {
        $this->_active->content->params($params);
    }
        
    public function partial($name, $params = array())
    {
        array_unshift($this->_active->renders, (object) [
            'script'    => $this->script("{$this->_active->script->path}{$this->_partialSeparator}{$name}"),
            'depth'     => 0,
            'params'    => $params,
            'isPartial' => true,
        ]);
        $content = $this->_render();
        array_shift($this->_active->renders);

        return $content;
    }

    public function parent($depth = 1)
    {
        $render = &$this->_active->renders[0];
        $render->depth += $depth;

        return $this->_render();
    }
        
    public function inner($path, array $params = array(), $group = null)
    {
        if (!isset($this->_active)) {
            throw new View\Exception("Cannot call View::inner() outside of rendering context");
        }

        $params = array_merge(
            $this->_active->params,
            $params
        );
        return $this->render($path, $params, $group);      
    }

    public function outer($path, array $params = array(), $group = null)
    {
        if (!isset($this->_active)) {
            throw new View\Exception("Cannot call View::outer() outside of rendering context");
        }
        
        $params = array_merge(
            $this->_active->params,
            $params
        );
        $this->_active->outer = [$path, $params, $group, false];
    }

    protected function block($name = null)
    {
        $content = $this->end();
        if (strlen($content) > 0 ) {
            $this->_active->content->block($this->_active->block, $content);
        }

        $name = isset($name)
            ? $name
            : $this->_active->content->next();

        $this->_active->block = $name;
        $this->start();
    }

    protected function content($name = null)
    {
        if (!isset($this->_active->previous)) {
            return;
        }
        return isset($name)
            ? $this->_active->previous->block($name)
            : $this->_active->previous;
    }
    
    protected function start()
    {
        ob_start();
        $this->_active->buffers++;
    }
    
    protected function end()
    {
        $this->_active->buffers--;
        return ob_get_clean();
    }

    public function escape($string)
    {
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    }

    public function encode($string)
    {
        return mb_convert_encoding($string, 'UTF-8');
    }

    public function strip($string)
    {
        return strip_tags($string);
    }

    public function execute(\Coast\Request $req, \Coast\Response $res)
    {        
        $path = $req->path();
        $path = '/' . (strlen($path) ? $path : 'index');
        if (isset($this->_inflector)) {
            $path = $this->_inflector($path, 'path');
        }

        try {
            return $res->html($this->render($path, [
                'req' => $req,
                'res' => $res,
            ]));
        } catch (View\Failure $e) {
            return;
        }
    }
} 
