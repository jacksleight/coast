<?php
namespace Coast;

use Coast\Sitemap;

class SitemapTest extends \PHPUnit_Framework_TestCase
{
    protected $_sitemap;
    protected $_output;

    public function setUp()
    {
        $this->_sitemap = new Sitemap();
        $this->_sitemap->add(
            new \Coast\Url('http://coastphp.com/'),
            new \DateTime('2014-01-01'),
            Sitemap::CHANGEFREQ_ALWAYS,
            1
        );
        $this->_output = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>http://coastphp.com/</loc><lastmod>2014-01-01T00:00:00+00:00</lastmod><changefreq>always</changefreq><priority>1.0</priority></url></urlset>
';
    }

    public function testString()
    {
        $this->assertEquals($this->_output, $this->_sitemap->toString());        
        $this->assertEquals($this->_output, (string) $this->_sitemap);        
    }

    public function testFile()
    {
        $file = \Coast\File::createTempoary();
        $this->_sitemap->writeFile($file);
        $file = $file->open('r');
        $this->assertEquals($this->_output, $file->read());
        $file = $file->close();
        $file->remove();
    }
}