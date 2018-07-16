<?php declare(strict_types=1);
namespace Morpho\Code\Compiler;

use Morpho\Base\Config;
use Morpho\Base\IFn;

// ILanguage (source/in) -> ILanguage (target/out)
interface ITranslator extends IFn {
    public function config(): Config;
}
