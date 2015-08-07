<?php
namespace Morpho\Web\View;

use Morpho\Base\ArrayTool;

class TagRenderer {
    public static function openTag($tagName, array $attributes = array(), $isXml = false) {
        return '<'
        . escapeHtml($tagName)
        . self::attributes($attributes)
        . ($isXml ? ' />' : '>');
    }

    public static function closeTag($name) {
        return '</' . escapeHtml($name) . '>';
    }

    /**
     * The source was found in Drupal-7.
     */
    public static function attributes(array $attributes) {
        foreach ($attributes as $attribute => &$data) {
            if (!is_numeric($attribute)) {
                $data = implode(' ', (array)$data);
                $data = $attribute . '="' . escapeHtml($data) . '"';
            }
        }

        return $attributes ? ' ' . implode(' ', $attributes) : '';
    }

    public static function renderSingle($tagName, array $attributes = null, array $options = []) {
        $options['isSingle'] = true;
        return self::render($tagName, $attributes, null, $options);
    }

    public static function render($tagName, array $attributes = null, $text = null, array $options = []) {
        $options = ArrayTool::handleOptions(
            $options,
            [
                'escapeText' => true,
                'isSingle' => false,
                'isXml' => null,
                'eol' => true,
            ]
        );
        $output = self::openTag($tagName, (array)$attributes, $options['isXml']);
        if (!$options['isSingle']) {
            $output .= $options['escapeText'] ? escapeHtml($text) : $text;
            $output .= self::closeTag($tagName);
        }
        if ($options['eol']) {
            $output .= "\n";
        }

        return $output;
    }
}
