# php-xml

XML-to-array conversion in PHP.

### Installation

With Composer:

```
$ composer require chrisullyott/php-xml
```

### Usage

```
$parser = new XmlParser('/path/to/feed.xml');

$items = $parser->getItems();

print_r($items);
```
