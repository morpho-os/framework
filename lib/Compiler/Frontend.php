<?php declare(strict_types=1);
namespace Morpho\Compiler;

use Morpho\Base\NotImplementedException;
use UnexpectedValueException;

class Frontend extends ConfigurablePipe implements IFrontend {
    public function __invoke(mixed $val): mixed {
        //$parser = $conf['parser'];
        return $val;
    }

    public function current(): callable {
        $index = $this->index;
        if ($index === 0) {
            return $this->parser();
        }
        if ($index === 1) {
            return $this->sema();
        }
        throw new UnexpectedValueException();
    }

    public function count(): int {
        // count([$this->parser(), $this->sema()])
        return 2;
    }

    /**
     * Returns parser. Parser may or may not include a lexer.
     * @return callable
     */
    public function parser(): callable {
        throw new NotImplementedException();
    }

    /**
     * Returns semantic analyzer
     */
    public function sema(): callable {
        throw new NotImplementedException();
    }
}