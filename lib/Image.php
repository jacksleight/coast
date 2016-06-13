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

    protected $_driver = 'gd';

    protected $_baseDir;

    protected $_outputDir;

    protected $_resolver;

    protected $_outputResolver;

    protected $_transforms = [];

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

    public function transform($name, $value = null)
    {
        if (isset($value)) {
            $this->_transforms[$name] = $value->bindTo($this);
            return $this;
        }
        return isset($this->_transforms[$name])
            ? $this->_transforms[$name]
            : null;
    }

    public function transforms(array $transforms = null)
    {
        if (isset($transforms)) {
            foreach ($transforms as $name => $value) {
                $this->transform($name, $value);
            }
            return $this;
        }
        return $this->_transforms;
    }

    public function __invoke($file, $transforms, array $params = array())
    {
        if (\Coast\is_array_assoc($transforms)) {
            $params = $transforms;
            $transforms = ['default'];
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
        } else if (!$file->isReadable()) {
            return $this->_resolver->file($file);
        }

        $transforms = (array) $transforms;
        foreach ($transforms as $i => $name) {
            if (!isset($this->_transforms[$name])) {
                throw new Image\Exception("Transform '{$name}' is not defined");
            }
            $transforms[$i] = (string) $name;
        }
        foreach ($params as $name => $value) {
            $params[(string) $name] = (string) $value;
        }
        
        $path   = $file->toRelative($this->_baseDir);
        $output = $this->_generateOutput($file, $transforms, $params);

        return $output->exists()
            ? (isset($this->_outputResolver)
                ? $this->_outputResolver->file($output) 
                : $this->_resolver->file($output))
            : $this->_resolver->string('image')->queryParams([
                'file'          => $path->name(),
                'transforms'    => $transforms,
                'params'        => $params,
            ]);
    }

    public function execute(Request $req, Response $res)
    {
        $parts = explode('/', $req->path());
        if ($parts[0] != 'image') {
            return;
        }

        $file = new File("{$this->_baseDir}/{$req->file}");
        $file = $file->toReal();
        if (!$file->isWithin($this->_baseDir)) {
            throw new Image\Exception("File '{$file}' is not within base directory '{$this->_baseDir}'");
        } else if (!$file->isReadable()) {
            throw new Image\Exception("File '{$file}' is not readable");
        }

        $transforms = $req->transforms;
        $params     = isset($req->params) ? $req->params : [];
        $output     = $this->_generateOutput($file, $transforms, $params);

        $image = $this->_manager->make($file->name());
        foreach ($transforms as $name) {
            $this->run($name, $image, $params);
        }
        $image->save($output->name(), isset($image->quality) ? $image->quality : 90);

        return $res->redirect(isset($this->_outputResolver)
            ? $this->_outputResolver->file($output)
            : $this->_resolver->file($output));
    }

    public function run($name, InterventionImage $image, array $params = array())
    {
        $this->_transforms[$name]($image, $params);
    }

    protected function _generateOutput(File $file, $transforms, array $params)
    {
        sort($transforms);
        ksort($params);
        $id = md5(
            $file->name() .
            $file->modifyTime()->getTimestamp() .
            serialize($transforms) .
            serialize($params)
        );
        return $this->_outputDir
            ->dir("{$id[0]}/{$id[1]}", true)
            ->file("{$id}.{$file->extName()}");
    }
}