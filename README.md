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
	],
	[                                           // Take everything inside [% %], split by | and shuffle choices
		'take' => ['[%', '%]'],
		'split' => '|',
		'transform' => function($choices) {
			shuffle($choices);
			return join('', $choices);
		}
	]
]);

$text = $ptt->compile($template);               // Compile template using this rules 
```

