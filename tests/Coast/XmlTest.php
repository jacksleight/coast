<?php
namespace Coast\Test;

use Coast\Xml;

class XmlTest extends \PHPUnit\Framework\TestCase
{
    protected $_xml;
    protected $_output;
    protected $_array;

    public function setUp()
    {
        $this->_xml = new Xml('<?xml version="1.0" encoding="UTF-8"?><response/>');
        $this->_xml->addChild('message', 'success');
        $this->_output = '<?xml version="1.0" encoding="UTF-8"?>
<response><message>success</message></response>
';
        $this->_array = [
            'message' => 'success',
        ];
    }

    public function testString()
    {
        $this->assertEquals($this->_output, $this->_xml->toString());        
        $this->assertEquals($this->_output, (string) $this->_xml);        
    }

    public function testFile()
    {
        $file = \Coast\File::createTemp();
        $this->_xml->writeFile($file);
        $file->open('r');
        $this->assertEquals($this->_output, $file->read());
        $file->close();
        $file->remove();
    }

    public function testArray()
    {
        $this->assertEquals($this->_array, $this->_xml->toArray());
    }
}