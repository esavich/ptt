# PTT - PHP Text Template compiler

Used for generating dynamic text from templates. Commonly used for seo text generation.

#### Basic usage:
```php
$template = 'Lorem ipsum [dolor sit|amet], consectetur adipisicing elit. Aliquid [aut et|expedita|fuga [fugiat|ipsum molestias] neque nesciunt] placeat quasi, quisquam repellat tempora totam. Amet blanditiis [corporis|esse|odio] soluta.';

$ptt = new Ptt([
	[                                           // This rule will take everything inside [], split it by | and
		'take' => ['[', ']'],                   //choose random variant
		'split' => '|',
		'transform' => function($choices) {
			return $choices[rand(0, count($choices) - 1)];
		}
	]
]);

// Separate rules may be added like this
$ptt->addRule([
	'take' => ['[%', '%]'],
	'split' => '|',
	'transform' => function($choices) {
		shuffle($choices);
		return join('', $choices);
	}
]);

// All rules by default has '[',']' as enclosing symbols and '|' set to splitting.

$text = $ptt->compile($template, [$replace]);               // Compile template using this rules

// Optional parameter $replace used for replacing placeholders in template after compiling it.
// By default placeholders enclosed in '<' and '>'. This could be changed with two first params in $replace.

// Full $replace example:
$replace = [
	'<', '>', [
		'placeholder1' => 'replacement1',
		'placeholder2' => 'replacement2'
	]
];

// As mentioned above all replacements happens after template compiling, so enclosing symbols should differ from that used in compile rules.
```
