<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Compiler\FrontEnd;

class Token {
    /**
     * @var string
     */
    public $tokenClass;

    /**
     * @var string
     */
    public $lexeme;

    /**
     * @var Location
     */
    public $location;

    /**
     * @var array
     */
    public $meta = [];
}
