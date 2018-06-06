<?php declare(strict_types=1);

namespace Morpho\Code\Js;

use Morpho\Base\IFn;
use PhpParser\ParserFactory;

class Parser implements IFn {
    /**
     * @param mixed $context
     * @return mixed
     */
    public function __invoke($context) {
        $source = $context['source'];
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($source);
        $context['ir'] = $ast;
        return $context;
    }
}
