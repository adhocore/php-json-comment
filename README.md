## adhocore/json-comment

[![Latest Version](https://img.shields.io/github/release/adhocore/php-json-comment.svg?style=flat-square)](https://github.com/adhocore/php-json-comment/releases)
[![Travis Build](https://img.shields.io/travis/adhocore/php-json-comment/main.svg?style=flat-square)](https://travis-ci.org/adhocore/php-json-comment?branch=main)
[![Scrutinizer CI](https://img.shields.io/scrutinizer/g/adhocore/php-json-comment.svg?style=flat-square)](https://scrutinizer-ci.com/g/adhocore/php-json-comment/?branch=main)
[![Codecov branch](https://img.shields.io/codecov/c/github/adhocore/php-json-comment/main.svg?style=flat-square)](https://codecov.io/gh/adhocore/php-json-comment)
[![StyleCI](https://styleci.io/repos/100117199/shield)](https://styleci.io/repos/100117199)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)


- Lightweight JSON comment stripper library for PHP.
- Makes possible to have comment in any form of JSON data.
- Supported comments: single line `// comment` or multi line `/* comment */`.
- Also strips trailing comma at the end of array or object, eg:
    - `[1,2,,]` => `[1,2]`
    - `{"x":1,,}` => `{"x":1}`

## Installation
```bash
composer require adhocore/json-comment

# for php5.6
composer require adhocore/json-comment:^0.2
```

## Usage
```php
use Ahc\Json\Comment;

// The JSON string!
$someJsonText = '{"a":1,
"b":2,// comment
"c":3 /* inline comment */,
// comment
"d":/* also a comment */"d",
/* creepy comment*/"e":2.3,
/* multi line
comment */
"f":"f1",}';

// OR
$someJsonText = file_get_contents('...');

// Strip only!
(new Comment)->strip($someJsonText);

// Strip and decode!
(new Comment)->decode($someJsonText);

// You can pass args like in `json_decode`
(new Comment)->decode($someJsonText, $assoc = true, $depth = 512, $options = JSON_BIGINT_AS_STRING);

// Or you can use static alias of decode:
Comment::parse($json, true);

# Or use file directly
Comment::parseFromFile('/path/to/file.json', true);
```
