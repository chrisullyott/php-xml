# php-xml

XML-to-array conversion in PHP. Built for pulling items from a variety of RSS feeds.

### Installation

With [Composer](https://getcomposer.org/):

```bash
$ composer require chrisullyott/php-xml
```

### Usage

```php
use ChrisUllyott\XmlParser;

$parser = new XmlParser('/path/to/feed.xml'); // or pass in a raw XML string

$items = $parser->getItems();

print_r($items);
```

Each feed item and their child attributes are parsed into a flat structure:

```
(
    [0] => Array
        (
            [title] => News for September the Second
            [link] => http://example.com/2002/09/01/news-for-september-the-second
            [guid] => 20020901-news-for-september-the-second
            [guid_isPermaLink] => false
            [description] => Things happened today!
        )

    [1] => Array
        (
            [title] => News for September the First
            [link] => http://example.com/2002/09/01/news-for-september-the-first
            [guid] => 20020901-news-for-september-the-first
            [guid_isPermaLink] => false
            [description] => Things happened today!
        )

)
```
