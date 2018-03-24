<?php

/**
 * Test for XmlParser
 */

use PHPUnit\Framework\TestCase;

class XmlParserTest extends TestCase
{
    private static $githubFields = [
        'title',
        'title_type',
        'link_href',
        'link_rel',
        'link_type',
        'link_title',
        'published',
        'updated',
        'id',
        'content',
        'content_type',
        'content_xml:base',
        'author_name',
        'summary',
        'summary_type',
        'media:thumbnail',
        'media:thumbnail_xmlns:media',
        'media:thumbnail_url'
    ];

    private static function getParser($filename)
    {
        return new XmlParser("tests/xml/{$filename}");
    }

    public function testParseGithub()
    {
        $parser = self::getParser('github.xml');
        $item = $parser->getItems()[0];
        $fields = array_keys($item);

        foreach (static::$githubFields as $field) {
            $this->assertContains($field, $fields);
        }
    }
}
