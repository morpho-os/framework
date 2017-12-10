<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\ArrayTool;

class Html {
    public static function encode($text): string {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Inverts result that can be obtained with escapeHtml().
     */
    public static function decode($text): string {
        return htmlspecialchars_decode($text, ENT_QUOTES);
    }

    public static function openTag(string $tagName, array $attributes = [], bool $isXml = false): string {
        return '<'
            . Html::encode($tagName)
            . self::attributes($attributes)
            . ($isXml ? ' />' : '>');
    }

    public static function closeTag(string $name): string {
        return '</' . Html::encode($name) . '>';
    }

    /**
     * The source was found in Drupal-7.
     */
    public static function attributes(array $attributes): string {
        foreach ($attributes as $attribute => &$data) {
            if (!is_numeric($attribute)) {
                $data = implode(' ', (array)$data);
                $data = $attribute . '="' . Html::encode($data) . '"';
            }
        }

        return $attributes ? ' ' . implode(' ', $attributes) : '';
    }

    public static function singleTag(string $tagName, array $attributes = null, array $options = []): string {
        $options['isSingle'] = true;
        return self::tag($tagName, $attributes, null, $options);
    }

    public static function tag(string $tagName, array $attributes = null, string $text = null, array $options = null): string {
        $options = ArrayTool::handleOptions(
            (array)$options,
            [
                'escapeText' => true,
                'isSingle'   => false,
                'isXml'      => false,
                'eol'        => true,
            ]
        );
        $output = self::openTag($tagName, (array)$attributes, $options['isXml']);
        if (!$options['isSingle']) {
            $output .= $options['escapeText'] ? self::encode($text) : $text;
            $output .= self::closeTag($tagName);
        }
        if ($options['eol']) {
            $output .= "\n";
        }

        return $output;
    }

    /**
     * @param array|\Traversable $options
     * @param array|\Traversable|scalar|null $selectedOption
     */
    public static function options($options, $selectedOption = null): string {
        $html = '';
        if (null === $selectedOption || is_scalar($selectedOption)) {
            $defaultValue = (string) $selectedOption;
            foreach ($options as $value => $text) {
                $value = (string) $value;
                $selected = $value === $defaultValue ? ' selected' : '';
                $html .= '<option value="' . Html::encode($value) . '"' . $selected . '>' . Html::encode($text) . '</option>';
            }
            return $html;
        }
        if (!is_array($selectedOption) && !$selectedOption instanceof \Traversable) {
            throw new \UnexpectedValueException();
        }
        $newOptions = [];
        foreach ($options as $value => $text) {
            $newOptions[(string) $value] = $text;
        }
        $selectedOptions = [];
        foreach ($selectedOption as $val) {
            $val = (string) $val;
            $selectedOptions[$val] = true;
        }
        foreach ($newOptions as $value => $text) {
            $selected = isset($selectedOptions[$value]) ? ' selected' : '';
            $html .= '<option value="' . Html::encode($value) . '"' . $selected . '>' . Html::encode($text) . '</option>';
        }
        return $html;
    }
}