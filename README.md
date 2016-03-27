# Sprout

Simple DOM generator inspired by [Emmet] [e].  Dumb and fluent.

## Usage

You can build your DOM from subtrees

```php
$head = Sprout\root('head')
    ->meta('charset="utf-8"')->join()
    ->up()
    ->title()->text('Title')
    ->root()
;

$body = Sprout\root('body')
    ->h1('id="header"')->text('Header')
    ->up()
    ->p()->text('Paragraph of text.')
    ->root()
;

echo Sprout\root('html', 'lang="en"')->insert($head, $body);
```

Or you can build entire tree using marked nodes

```php
echo Sprout\root('html', 'lang="en"')->mark('h')
    // head subtree
    // ...
    ->to('h')
    // body subtree
    // ...
    ->root()
;
```

[e]: http://emmet.io/
