<?php
/* 
 * Copyright 2015 Jack Sleight <http://jacksleight.com/>
 * This source file is subject to the MIT license that is bundled with this package in the file LICENCE. 
 */

namespace Coast;

use Coast\App\Access;
use Coast\App\Executable;
use Coast\Request;
use Coast\Response;
use Coast\Url;

class Csp implements Access, Executable
{
    use Access\Implementation;
    use Executable\Implementation;

    protected $_nonce;

    protected $_isReportOnly = false;

    protected $_reportUrl;

    protected $_groups = [];

    protected $_directives = [];

    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            if ($name[0] == '_') {
                throw new \Coast\Exception("Access to '{$name}' is prohibited");  
            }
            $this->$name($value);
        }
    }

    public function reportUrl(Url $reportUrl = null)
    {
        if (func_num_args() > 0) {
            $this->_reportUrl = $reportUrl;
            return $this;
        }
        return $this->_reportUrl;
    }

    public function isReportOnly($isReportOnly = null)
    {
        if (func_num_args() > 0) {
            $this->_isReportOnly = (bool) $isReportOnly;
            return $this;
        }
        return $this->_isReportOnly;
    }

    public function group($name, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_groups[$name] = $value;
            return $this;
        }
        return $this->_groups[$name];
    }

    public function groups(array $groups = null)
    {
        if (func_num_args() > 0) {
            foreach ($groups as $name => $value) {
                $this->group($name, $value);
            }
            return $this;
        }
        return $this->_groups;
    }

    public function directive($name, $value = null)
    {
        if (func_num_args() > 1) {
            $this->_directives[$name] = $value;
            return $this;
        }
        return $this->_directives[$name];
    }

    public function directives(array $directives = null)
    {
        if (func_num_args() > 0) {
            foreach ($directives as $name => $value) {
                $this->directive($name, $value);
            }
            return $this;
        }
        return $this->_directives;
    }

    public function groupSource($group, $value)
    {
        $this->_groups[$group][] = $value;
        return $this;
    }

    public function groupSources($group, array $values)
    {
        foreach ($values as $value) {
            $this->groupSource($group, $value);
        }
        return $this;
    }

    public function directiveSource($directive, $value)
    {
        $this->_directives[$directive][] = $value;
        return $this;
    }

    public function directiveSources($directive, array $values)
    {
        foreach ($values as $value) {
            $this->directiveSource($directive, $value);
        }
        return $this;
    }

    public function nonce()
    {
        if (!isset($this->_nonce)) {
            $this->_nonce = \Coast\str_random();
        }
        return $this->_nonce;
    }

    public function toString()
    {
        $parts = [];
        foreach ($this->_directives as $name => $sources) {
            $parts[] = "{$name} {$this->_parseSources($sources)}";
        }
        if (isset($this->_reportUrl)) {
            $parts[] = "report-uri {$this->_reportUrl}";
        }
        return implode('; ', $parts);
    }

    protected function _parseSources(array $sources)
    {
        if (!is_array($sources)) {
            $sources = [$sources];
        } 

        $parts = [];
        foreach ($sources as $i => $value) {
            if (!is_array($value) && isset($this->_groups[$value])) {
                $value = $this->_groups[$value];
            }
            if (is_array($value)) {
                $value = $this->_parseSources($value);
            } else if (preg_match('/^(none|self|unsafe-inline|unsafe-eval|(nonce|sha256|sha384|sha512)-.+)$/i', $value)) {
                $value = "'{$value}'";
            } else if ($value == 'nonce') {
                $value = "'nonce-{$this->nonce()}'";
            }
            $parts[] = $value;
        }

        return implode(' ', $parts);
    }

    public function postExecute(Request $req, Response $res)
    {
        $header = $this->_isReportOnly
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';
        $res->header($header, $this->toString());
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function __invoke()
    {
        return $this->toString();
    }
}