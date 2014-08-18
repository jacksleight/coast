<?php
/*
 * Copyright 2008-2014 Jack Sleight <http://jacksleight.com/>
 * Any redistribution or reproduction of part or all of the contents in any form is prohibited.
 */

namespace Coast\App;

use Coast\Request,
    Coast\Response;

class Image implements \Coast\App\Access, \Coast\App\Executable
{
    use \Coast\App\Access\Implementation;

    protected $_baseDir;

    protected $_outputDir;

    protected $_urlResolver;

    protected $_outputUrlResolver;

    protected $_transforms = [];

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
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

    public function urlResolver(\Coast\App\UrlResolver $urlResolver = null)
    {
        if (isset($urlResolver)) {
            $this->_urlResolver = $urlResolver;
            return $this;
        }
        return $this->_urlResolver;
    }

    public function outputUrlResolver(\Coast\App\UrlResolver $outputUrlResolver = null)
    {
        if (isset($outputUrlResolver)) {
            $this->_outputUrlResolver = $outputUrlResolver;
            return $this;
        }
        return $this->_outputUrlResolver;
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

    public function __invoke($file, $transforms = array(), array $params = array())
    {
        $file = !$file instanceof \Coast\File
            ? new \Coast\File("{$file}")
            : $file;
        $file = $file->isRelative()
            ? new \Coast\File("{$this->_baseDir}/{$file}")
            : $file;
        $file = $file->toReal();
        if (!$file->isWithin($this->_baseDir)) {
            throw new \Coast\App\Exception("File '{$file}' is not within base directory '{$this->_baseDir}'");
        } else if (!$file->isReadable()) {
            throw new \Coast\App\Exception("File '{$file}' is not readable");
        }

        $transforms = (array) $transforms;
        foreach ($transforms as $i => $name) {
            if (!isset($this->_transforms[$name])) {
                throw new \Coast\App\Exception("Transform '{$name}' is not defined");
            }
            $transforms[$i] = (string) $name;
        }
        foreach ($params as $name => $value) {
            $params[(string) $name] = (string) $value;
        }
        
        $path   = $file->toRelative($this->_baseDir);
        $output = $this->_generateOutput($file, $transforms, $params);

        return $output->exists()
            ? (isset($this->_outputUrlResolver)
                ? $this->_outputUrlResolver->file($output) 
                : $this->_urlResolver->file($output))
            : $this->_urlResolver->string('image')->queryParams([
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

        $file = new \Coast\File("{$this->_baseDir}/{$req->file}");
        $file = $file->toReal();
        if (!$file->isWithin($this->_baseDir)) {
            throw new \Coast\App\Exception("File '{$file}' is not within base directory '{$this->_baseDir}'");
        } else if (!$file->isReadable()) {
            throw new \Coast\App\Exception("File '{$file}' is not readable");
        }

        $transforms = $req->transforms;
        $params     = isset($req->params) ? $req->params : [];
        $output     = $this->_generateOutput($file, $transforms, $params);

        $image = new \Intervention\Image\Image($file->name());
        foreach ($transforms as $name) {
            $this->run($name, $image, $params);
        }
        $image->save($output->name());

        return $res->redirect(isset($this->_outputUrlResolver)
            ? $this->_outputUrlResolver->file($output)
            : $this->_urlResolver->file($output));
    }

    public function run($name, \Intervention\Image\Image $image, array $params = array())
    {
        $this->_transforms[$name]($image, $params);
    }

    protected function _generateOutput(\Coast\File $file, $transforms, array $params)
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

    public function lorempixel($width, $height = null, $category = null, $gray = false)
    {
        $parts = array();
        if ($gray) {
            $parts[] = 'g';
        }
        $parts[] = $width;
        $parts[] = isset($height) ? $height : $width;
        if (isset($category)) {
            $parts[] = $category;
        }
        return new \Coast\Url('http://lorempixel.com/' . implode('/', $parts) . '/');
    }

    public function unsplash($width, $height = null, $gray = false)
    {
        $parts = array();
        if ($gray) {
            $parts[] = 'g';
        }
        $parts[] = $width;
        $parts[] = isset($height) ? $height : $width;
        return new \Coast\Url('http://unsplash.it/' . implode('/', $parts) . '/?random');
    }
}