## adhocore/json-comment [![build status](https://travis-ci.org/adhocore/json-comment.svg?branch=master)](https://travis-ci.org/adhocore/json-comment)

- Lightweight JSON comment stripper library for PHP.
- Makes possible to have comment in any form of JSON data.

## Installation
```bash
composer require adhocore/json-comment
```

## Usage
```php
use Ahc\Json\Comment;

// The JSON string!
$someJsonText = '{...}';
$someJsonText = file_get_contents('...'');

// Strip only!
(new Comment)->strip($someJsonText);

// Strip and decode!
(new Comment)->decode($someJsonText);
```
