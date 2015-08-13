<?php
/**
 * Class TemplateReplacer
 */
class TemplateReplacer {
	protected $tpl;
	protected $split_sym = ['[', ']'];
	protected $variant_sym = '|';

	protected $place_rule;

	protected $tokens = [];
	protected $text = '';

	public function __construct($template) {
		$this->tpl = $template;
		$this->place_rule = function($variants) {
			return $variants[rand(0, count($variants) - 1)];
		};
	}

	/**
	 * Set begin and end letters for template
	 *
	 * @param $begin
	 * @param $end
	 */
	public function setSplitSym($begin, $end) {
		$this->split_sym[0] = $begin;
		$this->split_sym[1] = $end;
	}

	/**
	 * Set variant split symbol
	 *
	 * @param $sym
	 */
	public function setVariantSym($sym) {
		$this->variant_sym = $sym;
	}

	/**
	 * @param callable $callback
	 */
	public function setChooseCallback($callback) {
		$this->place_rule = $callback;
	}

	/**
	 * Parse and compile source template
	 */
	public function compile() {
		$stack = [];
		$this->tokens = [];

		foreach (str_split($this->tpl) as $letter) {
			if ($letter == $this->split_sym[1]) {
				$cur_token = '$'.(count($this->tokens)+1);
				$tmp = [];

				while (($sym = array_pop($stack)) != $this->split_sym[0]) {
					array_unshift($tmp, $sym);
				}

				array_push($stack, $cur_token);

				$this->tokens[$cur_token] = $tmp;
			} else {
				array_push($stack, $letter);
			}
		}

		$this->text = join('', $stack);
		$this->tokens = array_map(function($x) {
			return join('', $x);
		}, $this->tokens);
	}

	/**
	 * Format and return final version of text
	 *
	 * @return string
	 */
	public function getText() {
		$this->compile();

		foreach ($this->tokens as $key => $token) {
			$this->tokens[$key] = $this->replaceToken($token);
		}

		return $this->replaceToken($this->text);
	}

	/**
	 * Find all placeholders in one token piece and replace with corresponding template
	 *
	 * @param string $token Token to replace
	 * @return string
	 */
	protected function replaceToken($token) {
		if (preg_match_all('/\$\d+/', $token, $matches) > 0) {
			$matches = $matches[0];
			$place_method = $this->place_rule;
			foreach ($matches as $placeholder) {
				$tmp = explode($this->variant_sym, $this->tokens[$placeholder]);
				$token = str_replace($placeholder, $place_method($tmp), $token);
			}
		}

		return $token;
	}
}
