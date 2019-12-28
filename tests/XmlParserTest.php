<?php

/**
 * Tests for XmlParser.
 *
 * @author Chris Ullyott <chris@monkdevelopment.com>
 */

use ChrisUllyott\XmlParser;

class XmlParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test whether parsing gets us an array of items.
     */
    public function testParser()
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'sample.xml';

        $parser = new XmlParser($filename);

        $this->assertNotEmpty($parser->getItems());
    }
}
