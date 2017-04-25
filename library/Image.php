<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Coast;

use Coast\Request;
use Coast\Response;
use Coast\File;
use Coast\Url;
use Intervention\Image\Image as InterventionImage;
use Intervention\Image\ImageManager;

class Image implements \Coast\App\Access, \Coast\App\Executable
{
    use \Coast\App\Access\Implementation;
    use \Coast\App\Executable\Implementation;

    protected $_manager;

    protected $_prefix = null;

    protected $_driver = 'gd';

    protected $_baseDir;

    protected $_outputDir;

    protected $_resolver;

    protected $_outputResolver;

    protected $_quality = 80;

    protected $_actions = [];

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }

        $this->_manager = new ImageManager([
            'driver' => $this->_driver,
        ]);
    }

    public function prefix($prefix = null)
    {
        if (func_num_args() > 0) {
            $this->_prefix = $prefix;
            return $this;
        }
        return $this->_prefix;
    }

    public function driver($driver = null)
    {
        if (isset($driver)) {
            $this->_driver = $driver;
            return $this;
        }
        return $this->_driver;
    }

    public function baseDir(\Coast\Dir $baseDir = null)
    {
        if (isset($baseDir)) {
            $this->_baseDir = $baseDir;
            return $this;
        }
        return $this->_baseDir;
    }

    public function outputDir(\Coast\Dir $outputDir = null)
    {
        if (isset($outputDir)) {
            $this->_outputDir = $outputDir;
            return $this;
        }
        return $this->_outputDir;
    }

    public function resolver(\Coast\Resolver $resolver = null)
    {
        if (isset($resolver)) {
            $this->_resolver = $resolver;
            return $this;
        }
        return $this->_resolver;
    }

    public function outputResolver(\Coast\Resolver $outputResolver = null)
    {
        if (isset($outputResolver)) {
            $this->_outputResolver = $outputResolver;
            return $this;
        }
        return $this->_outputResolver;
    }

    public function quality($quality = null)
    {
        if (isset($quality)) {
            $this->_quality = $quality;
            return $this;
        }
        return $this->_quality;
    }

    public function action($name, $value = null)
    {
        if (isset($value)) {
            $this->_actions[$name] = $value->bindTo($this);
            return $this;
        }
        return isset($this->_actions[$name])
            ? $this->_actions[$name]
            : null;
    }

    public function actions(array $actions = null)
    {
        if (isset($actions)) {
            foreach ($actions as $name => $value) {
                $this->action($name, $value);
            }
            return $this;
        }
        return $this->_actions;
    }

    public function url($file, $actions = array('default'), array $params = array())
    {
        if (\Coast\is_array_assoc($actions)) {
            $params  = $actions;
            $actions = ['default'];
        }

        $file = !$file instanceof File
            ? new File("{$file}")
            : $file;
        $file = $file->isRelative()
            ? new File("{$this->_baseDir}/{$file}")
            : $file;
        $file = $file->toReal();
        if (!$file->isWithin($this->_baseDir)) {
            throw new Image\Exception("File '{$file}' is not within base directory '{$this->_baseDir}'");
        } else if (!$file->isReadable() || !in_array(strtolower($file->extName()), ['jpg', 'jpeg', 'png', 'gif'])) {
            return $this->_resolver->file($file);
        }

        if (!is_array($actions)) {
            $actions = array_map('trim', explode(',', $actions));
        }
        foreach ($params as $name => $value) {
            $params[(string) $name] = (string) $value;
        }
        
        $output = $this->_generateOutput($file, $actions, $params);

        return $output->exists()
            ? (isset($this->_outputResolver)
                ? $this->_outputResolver->file($output) 
                : $this->_resolver->file($output))
            : $this->_resolver->string($this->_prefix)->queryParams([
                'file'    => $file->toRelative($this->_baseDir)->name(),
                'actions' => $actions,
                'params'  => $params,
            ]);
    }

    public function process($file, $actions = array('default'), array $params = array())
    {
        if (\Coast\is_array_assoc($actions)) {
            $params  = $actions;
            $actions = ['default'];
        }

        $file = !$file instanceof File
            ? new File("{$file}")
            : $file;
        $file = $file->isRelative()
            ? new File("{$this->_baseDir}/{$file}")
            : $file;
        $file = $file->toReal();
        if (!$file->isWithin($this->_baseDir)) {
            throw new Image\Exception("File '{$file}' is not within base directory '{$this->_baseDir}'");
        } else if (!$file->isReadable() || !in_array(strtolower($file->extName()), ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Image\Exception("File '{$file}' is not readable or not in a supported format");
        }

        if (!is_array($actions)) {
            $actions = array_map('trim', explode(',', $actions));
        }

        $output = $this->_generateOutput($file, $actions, $params);

        $image = $this->_manager->make($file->name());
        foreach ($actions as $name) {
            $this->run($name, $image, $params);
        }
        $image->save($output->name(), isset($image->quality) ? $image->quality : $this->_quality);

        return $output;
    }

    public function run($name, InterventionImage $image, array $params = array())
    {
        if (!isset($this->_actions[$name])) {
            throw new Image\Exception("Transform '{$name}' is not defined");
        }
        $this->_actions[$name]($image, $params);
    }

    public function execute(Request $req, Response $res)
    {
        $parts = explode('/', $req->path());
        if ($parts[0] != $this->_prefix) {
            return;
        }

        $output = $this->process($req->file, $req->actions, isset($req->params) ? $req->params : []);

        return isset($this->_outputResolver)
            ? $res->redirect($this->_outputResolver->file($output))
            : $res->redirect($this->_resolver->file($output));
    }

    public function __invoke($file, $actions = array('default'), array $params = array())
    {
        return $this->url($file, $actions, $params);
    }

    protected function _generateOutput(File $file, $actions, array $params)
    {
        sort($actions);
        ksort($params);
        $id = md5(
            $file->name() .
            $file->modifyTime()->getTimestamp() .
            serialize($actions) .
            serialize($params)
        );
        return $this->_outputDir
            ->dir("{$id[0]}/{$id[1]}", true)
            ->file("{$id}.{$file->extName()}");
    }
}