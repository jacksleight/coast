<?php
namespace Coast\Test;

use Coast\Sitemap;

class SitemapTest extends \PHPUnit_Framework_TestCase
{
    protected $_sitemap;
    protected $_output;

    public function setUp()
    {
        $this->_sitemap = new Sitemap();
        $this->_sitemap->urls[] = (new Sitemap\Url())->fromArray([
            'url'             => new \Coast\Url('http://coastphp.com/'),
            'updateDate'      => new \DateTime('2014-01-01'),
            'changeFrequency' => Sitemap\Url::CHANGEFREQUENCY_ALWAYS,
            'priority'        => 0.5
        ]);
        $this->_output = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>http://coastphp.com/</loc><lastmod>2014-01-01T00:00:00+00:00</lastmod><changefreq>always</changefreq><priority>0.5</priority></url></urlset>
';
    }

    public function testString()
    {
        $this->assertEquals($this->_output, $this->_sitemap->toXml()->toString());
        $this->assertEquals($this->_output, (string) $this->_sitemap->toXml());
    }

    public function testFile()
    {
        $file = \Coast\File::createTemp();
        $this->_sitemap->toXml()->writeFile($file);
        $file->open('r');
        $this->assertEquals($this->_output, $file->read());
        $file->close();
        $file->remove();
    }
}