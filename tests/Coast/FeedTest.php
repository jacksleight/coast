<?php
namespace Coast\Test;

use Coast\Feed;

class FeedTest extends \PHPUnit_Framework_TestCase
{
    protected $_feed;
    protected $_output;

    public function setUp()
    {
        $this->_feed = new Feed(
            'Coast',
            new \Coast\Url('http://coastphp.com/'),
            'Jack Sleight',
            new \DateTime('2014-01-01')
        );
        $this->_feed->add(
            'Example Article',
            new \Coast\Url('http://coastphp.com/'),
            new \DateTime('2014-01-01'),
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est, sunt.',
            'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dignissimos, ut sequi labore consequuntur porro illo nisi, vero in molestias non beatae placeat, officia repellat quas eligendi dolor facere, nulla expedita!'
        );
        $this->_output = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"><title>Coast</title><id>http://coastphp.com/</id><link href="http://coastphp.com/"/><author><name>Jack Sleight</name></author><updated>2014-01-01T00:00:00+00:00</updated><entry><id>http://coastphp.com/</id><title><![CDATA[Example Article]]></title><link href="http://coastphp.com/"/><updated>2014-01-01T00:00:00+00:00</updated><summary type="html"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est, sunt.]]></summary><content type="html"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dignissimos, ut sequi labore consequuntur porro illo nisi, vero in molestias non beatae placeat, officia repellat quas eligendi dolor facere, nulla expedita!]]></content></entry></feed>
';
    }

    public function testString()
    {
        $this->assertEquals($this->_output, $this->_feed->toXml()->toString());        
        $this->assertEquals($this->_output, (string) $this->_feed->toXml());        
    }

    public function testFile()
    {
        $file = \Coast\File::createTemp();
        $this->_feed->toXml()->writeFile($file);
        $file->open('r');
        $this->assertEquals($this->_output, $file->read());
        $file->close();
        $file->remove();
    }
}