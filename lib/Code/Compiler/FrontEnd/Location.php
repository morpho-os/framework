<?php declare(strict_types=1);
namespace Morpho\Code\Compiler\FrontEnd;

class Location {
    /**
     * @var int
     */
    public $lineNo;
    /**
     * @var int
     */
    public $columnNo;
    /**
     * @var string
     */
    public $filePath;
}
