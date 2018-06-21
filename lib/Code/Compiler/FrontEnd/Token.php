<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler\FrontEnd;

class Token {
    /**
     * AKA Token-class
     * @var string
     */
    public $type;

    /**
     * AKA Lexeme
     * @var string
     */
    public $value;

    /**
     * @TODO: ['offset' => $offset, 'length' => $length], see also another formats in [esprima](https://github.com/ariya/esprima/blob/master/docs/lexical-analysis.md)
     * @var Location
     */
    public $location;

    /**
     * @var array
     */
    public $meta = [];
}
