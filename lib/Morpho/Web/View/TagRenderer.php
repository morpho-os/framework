<?php
namespace Morpho\Web\View;

use Morpho\Base\ArrayTool;
use function Morpho\Base\escapeHtml;

class TagRenderer {
    public static function openTag(string $tagName, array $attributes = [], bool $isXml = false): string {
        return '<'
        . escapeHtml($tagName)
        . self::attributes($attributes)
        . ($isXml ? ' />' : '>');
    }

    public static function closeTag(string $name): string {
        return '</' . escapeHtml($name) . '>';
    }

    /**
     * The source was found in Drupal-7.
     */
    public static function attributes(array $attributes): string {
        foreach ($attributes as $attribute => &$data) {
            if (!is_numeric($attribute)) {
                $data = implode(' ', (array)$data);
                $data = $attribute . '="' . escapeHtml($data) . '"';
            }
        }

        return $attributes ? ' ' . implode(' ', $attributes) : '';
    }

    public static function renderSingle(string $tagName, array $attributes = null, array $options = []): string {
        $options['isSingle'] = true;
        return self::render($tagName, $attributes, null, $options);
    }

    public static function render(string $tagName, array $attributes = null, string $text = null, array $options = null): string {
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
            $output .= $options['escapeText'] ? escapeHtml($text) : $text;
            $output .= self::closeTag($tagName);
        }
        if ($options['eol']) {
            $output .= "\n";
        }

        return $output;
    }
}
