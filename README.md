# PTT - PHP Text Template compiler

Used for generating dynamic text from templates. Commonly used for seo text generation.

#### Basic usage:
```php
$template = 'Lorem ipsum [dolor sit|amet], consectetur adipisicing elit. Aliquid [aut et|expedita|fuga [fugiat|ipsum molestias] neque nesciunt] placeat quasi, quisquam repellat tempora totam. Amet blanditiis [corporis|esse|odio] soluta.';

$replacer = new TemplateReplacer($template);
$replacer->setBoundarySym('[', ']');                // Set begin and end template symbols
$replacer->setVariantSym('|');                      // Set variant splitter
$replacer->setChooseCallback(function($variants) {  // Set callback that will be executed on every template piece
    return $variants[rand(0, count($variants) - 1)];
});

$text = $replacer->getText();                       // Compile and replace all templated entries with chosen variant
```
All current parameters is set by default. Other symbols may be specified if you have other template delimiters.
> It will replace everything inside "["  "]" to variant returned by the choose callback.
