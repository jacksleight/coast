<?php
namespace Coast\Test;

use Coast\Feed;

class FeedTest extends \PHPUnit\Framework\TestCase
{
    protected $_feed;
    protected $_output;

    public function setUp()
    {
        $this->_feed = new Feed();
        $this->_feed->fromArray([
            'id'         => '1000',
            'url'        => new \Coast\Url('http://coastphp.com/'),
            'urlFeed'    => new \Coast\Url('http://coastphp.com/'),
            'title'      => 'Coast',
            'updateDate' => new \DateTime('2014-01-01'),
            'author'     => ['name' => 'Jack Sleight'],
        ]);
        $this->_feed->items[] = (new Feed\Item())->fromArray([
            'id'         => '1000',
            'url'        => new \Coast\Url('http://coastphp.com/'),
            'title'      => 'Example Article',
            'updateDate' => new \DateTime('2014-01-01'),
            'author'     => ['name' => 'Jack Sleight'],
            'summary'    => ['content' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est, sunt.'],
            'body'       => ['content' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dignissimos, ut sequi labore consequuntur porro illo nisi, vero in molestias non beatae placeat, officia repellat quas eligendi dolor facere, nulla expedita!'],
        ]);
        $this->_output = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"><id>1000</id><link href="http://coastphp.com/"/><link rel="self" href="http://coastphp.com/"/><title><![CDATA[Coast]]></title><updated>2014-01-01T00:00:00+00:00</updated><author><name><![CDATA[Jack Sleight]]></name></author><entry><id>1000</id><title><![CDATA[Example Article]]></title><link href="http://coastphp.com/"/><updated>2014-01-01T00:00:00+00:00</updated><author><name><![CDATA[Jack Sleight]]></name></author><summary type="html"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est, sunt.]]></summary><content type="html"><![CDATA[Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dignissimos, ut sequi labore consequuntur porro illo nisi, vero in molestias non beatae placeat, officia repellat quas eligendi dolor facere, nulla expedita!]]></content></entry></feed>
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