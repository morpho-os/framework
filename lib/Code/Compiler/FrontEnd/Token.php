<?php declare(strict_types=1);
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
