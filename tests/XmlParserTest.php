<?php

/**
 * Test for XmlParser
 *
 * @todo Need a better way of getting all the "item" elements. #getItemsByTagName
 * needs to check the name of the tag every time and this isn't a great way to
 * handle the recursion.
 *
 * @todo Tag is "complete" but has no value, an empty key should not
 * make it into the resulting array... only its child values
 *
 * @todo #buildAttributeValues should work in the same way for child tags as well,
 * for example:
 * <author><name>John</name></author> = "author_name"
 * <author name="John"/> = "author_name"
 *
 * @todo Join multiple tags into one. Currently only the last one will be seen:
 * <category>One</category>
 * <category>Two</category>
 * <category>three</category>
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
