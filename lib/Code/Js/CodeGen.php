<?php declare(strict_types=1);
namespace Morpho\Code\Js;

use Morpho\Base\IFn;

class CodeGen implements IFn {
    /**
     * @param mixed $context
     * @return mixed
     */
    public function __invoke($context) {
        $ir = $context['ir'];
//        d($ir);
        //$context['targetProgram'] = $targetProgram;
        return '';
    }
}
