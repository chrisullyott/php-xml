<?php

/**
 * Parse XML documents using xml_parse_into_struct().
 *
 * http://php.net/manual/en/function.xml-parse-into-struct.php
 *
 * @author Chris Ullyott <chris@monkdevelopment.com>
 */
class XmlParser
{
    /**
     * The path to an XML file.
     *
     * @var string
     */
    private $file;

    /**
     * The expected name of the XML item tag.
     *
     * @var string
     */
    private $itemTagName;

    /**
     * A well-formed XML string.
     *
     * @var string
     */
    private $xmlString;

    /**
     * The SimpleXML object.
     *
     * @var SimpleXMLElement
     */
    private $xmlObject;

    /**
     * The raw XML structure.
     *
     * @var array
     */
    private $xmlStructure;

    /**
     * The parsed XML array.
     *
     * @var array
     */
    private $xmlArray;

    /**
     * Constructor.
     *
     * @param string $file        The path to an XML file
     * @param string $itemTagName The expected name of the XML item tag
     */
    public function __construct($file, $itemTagName = null)
    {
        $this->file = $file;
        $this->itemTagName = $itemTagName;
    }

    /**
     * Get the path to an XML file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get the XML item tag name.
     *
     * @return string
     */
    public function getItemTagName()
    {
        return $this->itemTagName;
    }

    /**
     * Get or detect the appropriate XML item tag name.
     *
     * @return string
     */
    public function getOrDetectItemTagName()
    {
        if (!$this->getItemTagName()) {
            return self::detectItemTagName($this->getXmlString());
        }

        return $this->getItemTagName();
    }

    /**
     * Get the raw XML string.
     *
     * @return string
     */
    public function getXmlString()
    {
        if (is_null($this->xmlString)) {
            $this->xmlString = file_get_contents($this->getFile());
            $this->xmlString = self::sanitizeXmlString($this->xmlString);
        }

        return $this->xmlString;
    }

    /**
     * Get the SimpleXML object.
     *
     * @return SimpleXMLElement
     */
    public function getXmlObject()
    {
        if (is_null($this->xmlObject)) {
            $this->xmlObject = self::parseXmlIntoObject($this->getXmlString());
        }

        return $this->xmlObject;
    }

    /**
     * Parse an XML string into its basic structure.
     *
     * @return array
     */
    public function getXmlStructure()
    {
        if (is_null($this->xmlStructure)) {
            $parser = xml_parser_create();
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
            xml_parse_into_struct($parser, $this->getXmlString(), $this->xmlStructure);
            xml_parser_free($parser);
        }

        return $this->xmlStructure;
    }

    /**
     * Get the parsed XML array.
     *
     * @return array
     */
    public function getXmlArray()
    {
        if (is_null($this->xmlArray)) {
            $this->xmlArray = self::xmlStructureToArray($this->getXmlStructure());
        }

        return $this->xmlArray;
    }

    /**
     * Detect the appropriate XML item tag name. Detects common item tags or falls
     * back to the most common parent tag's name.
     *
     * @return string
     */
    public function detectItemTagName()
    {
        $xmlObject = $this->getXmlObject();

        if (isset($xmlObject->channel->item)) {
            // RSS format.
            return 'item';
        } elseif (isset($xmlObject->entry)) {
            // Atom format.
            return 'entry';
        }

        return $this->getCommonParentTagName();
    }

    /**
     * Get the name of the most common parent tag.
     *
     * @return string
     */
    public function getCommonParentTagName()
    {
        $parentNames = [];

        foreach ($this->getXmlStructure() as $node) {
            if ($node['type'] === 'open') {
                $parentNames[] = $node['tag'];
            }
        }

        $count = array_count_values($parentNames);

        return array_search(max($count), $count);
    }

    /**
     * Get an array of content items. If a node name is not passed, items belonging
     * to the most frequently occurring node name are returned, essentially assuming
     * that the XML feed's content items are the most populous of all nodes.
     *
     * @return array
     */
    public function getItems()
    {
        $tagName = $this->getOrDetectItemTagName();

        return self::getItemsByTagName($this->getXmlArray(), $tagName);
    }

    /**
     * Parse an XML structure into a cohesive array of parent and child nodes.
     *
     * @param  array  $xmlStructure The raw XML structure
     * @return array
     */
    private static function xmlStructureToArray(array $xmlStructure)
    {
        $elements = [];
        $stack = [];

        foreach ($xmlStructure as $node) {
            $index = count($elements);

            if ($node['type'] === 'open' || $node['type'] === 'complete') {
                $tag = isset($node['tag']) ? $node['tag'] : null;
                $value = isset($node['value']) ? $node['value'] : null;
                $attributes = isset($node['attributes']) ? $node['attributes'] : null;

                $elements[$index] = [
                    'tag' => $tag,
                    'value' => $value,
                    'attributes' => $attributes
                ];

                if ($node['type'] === 'open') {
                    $elements[$index]['children'] = [];
                    $stack[count($stack)] = &$elements;
                    $elements = &$elements[$index]['children'];
                }
            }

            if ($node['type'] === 'close') {
                $elements = &$stack[count($stack) - 1];
                unset($stack[count($stack) - 1]);
            }
        }

        return $elements[0];
    }

    /**
     * Build an array of arrays representing the nodes of a given name.
     *
     * @param  array  $xmlArray The array of parsed XML
     * @param  string $tagName  The name of the node to filter by
     * @return array
     */
    private static function getItemsByTagName(array $xmlArray, $tagName)
    {
        $items = [];

        if (!empty($xmlArray['children']) && $xmlArray['tag'] === $tagName) {
            $itemValues = [];

            foreach ($xmlArray['children'] as $i) {
                $itemValues = array_merge($itemValues, self::buildItemValues($i));
            }

            $items[] = array_map('trim', $itemValues);
        }

        foreach ($xmlArray as $value) {
            if (is_array($value)) {
                $nextItems = self::getItemsByTagName($value, $tagName);
                $items = array_merge($items, $nextItems);
            }
        }

        return $items;
    }

    /**
     * Build an array of item values from a node array.
     *
     * @param  array  $nodeArray A parsed XML node
     * @return array
     */
    private static function buildItemValues(array $nodeArray)
    {
        $itemValues = [];

        $tag = $nodeArray['tag'];
        $value = $nodeArray['value'];
        $attributes = $nodeArray['attributes'];

        $itemValues[$tag] = $value;

        if ($attributes) {
            $attributeValues = self::buildAttributeValues($tag, $attributes);
            $itemValues = array_merge($itemValues, $attributeValues);
        }

        return $itemValues;
    }

    /**
     * Build additional item values from node attributes.
     *
     * @param  string $tagName        The XML tag name
     * @param  array  $attributeArray A parsed node attributes array
     * @return array
     */
    private static function buildAttributeValues($tagName, array $attributeArray)
    {
        $item = [];

        foreach ($attributeArray as $key => $value) {
            $attributeKey = "{$tagName}_{$key}";
            $item[$attributeKey] = $value;
        }

        return $item;
    }

    /**
     * Parse an XML string into a SimpleXML object.
     *
     * @param  string $xmlString A well-formed XML string
     * @return string
     */
    private static function parseXmlIntoObject($xmlString)
    {
        libxml_use_internal_errors(true);
        $xmlObj = simplexml_load_string($xmlString);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        if ($errors) {
            $message = strtok($errors[0]->message, "\n");
            $line = $errors[0]->line;
            throw new Exception("XML parse error: {$message} (Line {$line})");
        } else {
            return $xmlObj;
        }
    }

    /**
     * Sanitize an XML string.
     *
     * @param  string $xmlString A well-formed XML string
     * @return string
     */
    private static function sanitizeXmlString($xmlString)
    {
        if (!$xmlString) {
            return '';
        }

        $config = ['input-xml' => true, 'output-xml' => true];

        if ($result = tidy_repair_string($xmlString, $config, 'utf8')) {
            return $result;
        }

        throw new Exception('Failed to repair string with Tidy');
    }
}
