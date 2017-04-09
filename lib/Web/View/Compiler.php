<?php
namespace Morpho\Web\View;

use Zend\Filter\AbstractFilter as BaseFilter;
use PhpParser\Parser\Php7 as Parser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard as PrettyPrinter;

class Compiler extends BaseFilter {
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

    public function filter($code, bool $print = true) {
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