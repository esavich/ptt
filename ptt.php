<?php

/**
 * PHP Text templater
 */
class Ptt
{
    protected $rules = array();

    protected $tags = array();

    public function __construct($rules = array())
    {
        $this->rules = $rules;
    }

    /**
     * Add new rule to collection
     * @param array $rule Rule description
     */
    public function addRule($rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * Compile templates
     * @param string $tpl Source template
     * @param array $replace List of parameters that should be replaced
     * @return string Compiled text
     */
    public function compile($tpl, $replace = array())
    {
        $this->fulfillRules();
        $this->sortRules();
        $this->prepareTags();

        if (!$this->templateValid($tpl)) {
            return false;
        }

        $tree = null;

        $this->buildTree($tpl, $tree);

        $text = $this->assembleText($tree);

        if (count($replace) > 0) {
            $text = $this->doReplace($text, $replace);
        }

        return $text;
    }

    /**
     * Replace static text
     * @param string $tpl Source template
     * @param array $rules List of replace rules
     * @return string Template with replaced placeholders.
     */
    protected function doReplace($tpl, $rules)
    {
        if (count($rules) < 3) {
            $srs = '<';
            $ers = '>';
            $patts = $rules[0];
        } elseif (is_array($rules[2])) {
            $srs = $rules[0];
            $ers = $rules[1];
            $patts = $rules[2];
        } else {
            return $tpl;
        }

        foreach ($patts as $key => $value) {
            $tpl = $this->mb_stri_replace($srs . $key . $ers, $value, $tpl);
        }

        return $tpl;
    }

    /**
     * Assemble text from tree, transforming patterns according to rules
     * @param array $root Tag tree root
     * @return string Assembled part of text
     */
    protected function assembleText($root)
    {
        $accum = '';

        foreach ($root as $value) {
            if (!is_array($value)) {
                $accum .= $value;
            } else {
                if (isset($value['patt'])) {
                    $rule = $this->rules[$value['ind']];
                    $choices = explode($rule['split'], $accum . $value['patt']);

                    $accum = $rule['transform']($choices);
                } else {
                    $accum .= $this->assembleText($value);
                }
            }
        }

        return $accum;
    }

    /**
     * Create pattern tree from template
     * @param string $tpl Current processing template
     * @param array $tree Tree level
     * @return string unparsed part of template
     */
    protected function buildTree($tpl, &$tree)
    {
        $opening = array_map(function ($x) {
            return $x[0];
        }, $this->tags);

        $closing = array_map(function ($x) {
            return $x[1];
        }, $this->tags);

        if ($tree == null) {
            $tree = array();
        }

        $cc = 0;

        while (true) {
            list($pos, $index) = $this->matchFirstAny($opening, $tpl);
            list($epos, $eindex) = $this->matchFirstAny($closing, $tpl);

            if ($index != -1 && $pos < $epos) {
                $tree[$cc++] = substr($tpl, 0, $pos);
                $olen = strlen($opening[$index]);
                $tpl = substr($tpl, $pos + $olen);

                $tree[$cc] = null;

                $tpl = $this->buildTree($tpl, $tree[$cc++]);
            } elseif ($eindex != -1) {
                $tree[$cc++] = array('ind' => $eindex, 'patt' => substr($tpl, 0, $epos));
                $olen = strlen($closing[$eindex]);
                $tpl = substr($tpl, $epos + $olen);

                return $tpl;
            } else {
                $tree[$cc++] = $tpl;
                return $tpl;
            }
        }
    }

    /**
     * Search for first occurance of any variant
     * @param array $vars List of variables to match
     * @param string $tpl Template to search in
     * @return array [minimum value, $vars index of mathed value]
     */
    protected function matchFirstAny($vars, $tpl)
    {
        $min = mb_strlen($tpl);
        $min_ind = -1;

        foreach ($vars as $ind => $var) {
            $pos = strpos($tpl, $var);
            if ($pos !== false && $pos < $min) {
                $min = $pos;
                $min_ind = $ind;
            }
        }

        return array($min, $min_ind);
    }

    /**
     * Extract 'tags' from rules
     */
    protected function prepareTags()
    {
        foreach ($this->rules as $rule) {
            if (isset($rule['take'])) {
                $this->tags[] = $rule['take'];
            }
        }
    }

    /**
     * Sort rules by its length dec.
     * This is necessary for cases when two rules has same symbols
     */
    protected function sortRules()
    {
        usort($this->rules, function ($a, $b) {
            return max(array_map(function ($x) {
                return strlen($x);
            }, $a['take'])) >
            max(array_map(function ($x) {
                return strlen($x);
            }, $b['take'])) ? -1 : 1;
        });
    }

    /**
     * Fill all required fields in rules
     */
    protected function fulfillRules()
    {
        foreach ($this->rules as &$rule) {
            if (!isset($rule['take'])) {
                $rule['take'] = array('[', ']');
            }

            if (!isset($rule['split'])) {
                $rule['split'] = '|';
            }

            if (!isset($rule['transform'])) {
                $rule['transform'] = function ($choices) {
                    return $choices[rand(0, count($choices) - 1)];
                };
            }
        }
    }

    /**
     * Replace all matches of $pattern to $replacement.
     * Case-insensetive variant.
     * Works like php str_replace but with support for multibyte
     */
    protected function mb_stri_replace($pattern, $replacement, $string)
    {
        mb_internal_encoding('UTF-8');

        $pos = mb_stripos($string, $pattern, 0);
        $len = mb_strlen($pattern);

        while ($pos !== false) {
            $string = mb_substr($string, 0, $pos) . $replacement . mb_substr($string, $pos + $len);

            $pos = mb_stripos($string, $pattern, $pos);
        }

        return $string;
    }

    /**
     * @param string $template
     * @return bool
     */
    protected function templateValid($template)
    {
        foreach ($this->rules as $rule) {
            if (substr_count($template, $rule['take'][0]) != substr_count($template, $rule['take'][1])) {
                return false;
            }
        }

        return true;
    }
}
