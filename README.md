# Sprout [![Build Status](https://travis-ci.org/dmitrivereshchagin/sprout.svg?branch=master)](https://travis-ci.org/dmitrivereshchagin/sprout)

Simple DOM generator inspired by [Emmet](http://emmet.io/).
Dumb and fluent.

## Install

```
% composer require dmitrivereshchagin/sprout
```

## Usage

You can build your DOM from subtrees in the following way (you should
avoid recursion though)

```php
use Sprout\Node as Root;

$head = Root::create('head')
    ->meta('charset="utf-8"')->merge()
    ->up()
    ->title()->text('Title')
    ->root()
;

$body = Root::create('body')
    ->h1('id="header"')->text('Header')
    ->up()
    ->p()->text('Paragraph of text.')->times(2)
    ->root()
;

echo Root::create('html', 'lang="en"')
    ->insert($head, $body)
;
```

Or you can build entire tree using marked nodes

```php
echo Root::create('html', 'lang="en"')->mark('h')
    // head subtree
    // ...
    ->to('h')
    // body subtree
    // ...
    ->root()
;
```
## Testing

```
% composer test
```
