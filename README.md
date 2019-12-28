# php-xml

XML-to-array conversion in PHP. Built for pulling items from a variety of RSS feeds.

### Installation

With [Composer](https://getcomposer.org/):

```bash
$ composer require chrisullyott/php-xml
```

### Usage

```php
$parser = new XmlParser('/path/to/feed.xml');

$items = $parser->getItems();

print_r($items);
```

```
(
    [0] => Array
        (
            [title] => News for September the Second
            [link] => http://example.com/2002/09/01
            [description] => Things happened today!
        )

    [1] => Array
        (
            [title] => News for September the First
            [link] => http://example.com/2002/09/02
            [description] => Things happened today!
        )

)
```
