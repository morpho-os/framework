<?php

declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler\Frontend;

use Morpho\Base\Enum;

/**
 * https://en.wikipedia.org/wiki/Punctuation
 */
class Lexeme extends Enum {
    // Double quote
    public const DOUBLE_Q = '"';
    // Back quote
    public const BACK_Q = '`';
    // Apostrophe
    public const APOST = "'";
    // Opening parenthesis
    public const OPEN_PAREN = '(';
    // Closing parenthesis
    public const CLOSE_PAREN = ')';
    // Opening square bracket
    public const OPEN_BRACKET = '[';
    // Closing square bracket
    public const CLOSE_BRACKET = ']';
    // Opening curly brace
    public const OPEN_BRACE = '{';
    // Closing curly brace
    public const CLOSE_BRACE = '}';
    // Opening angle bracket
    public const OPEN_ANGLE_BRACKET = '<';
    // Closing angle bracket
    public const CLOSE_ANGLE_BRACKET = '>';
    public const COMMA = ',';
    public const DOT = '.';
    public const HYPHEN = '-';
    public const SEMICOLON = ';';
}