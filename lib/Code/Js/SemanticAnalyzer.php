<?php declare(strict_types=1);
namespace Morpho\Code\Js;

use Morpho\Base\IFn;

class SemanticAnalyzer implements IFn {
    /**
     * @param mixed $context
     * @return mixed
     */
    public function __invoke($context) {
        return $context;
    }
}
