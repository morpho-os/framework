<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use Morpho\Base\IFn;
use PhpParser\Parser\Php7 as Parser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

class Compiler implements IFn {
    protected $filePath;

    protected $appendSourceInfo = true;

    public function setFilePath($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * @param null|bool $flag
     * @return bool
     */
    public function appendSourceInfo($flag = null) {
        if (null !== $flag) {
            $this->appendSourceInfo = $flag;
        }
        return $this->appendSourceInfo;
    }

    public function __invoke($code, bool $print = true) {
        $parser = new Parser(new Lexer());
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(
            new Processor($this->filePath, $this, $this->appendSourceInfo)
        );
        $modified = $traverser->traverse($ast);

        if (!$print) {
            return $modified;
        }

        return (new PrettyPrinter())->prettyPrintFile($modified);
    }
}