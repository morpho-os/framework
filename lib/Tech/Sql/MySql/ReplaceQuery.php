<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\MySql;

use Morpho\Tech\Sql\IQuery;

class ReplaceQuery implements IQuery {
    use TQuery;
/*    public function build(): array {
        throw new NotImplementedException();
    }*/
    public function sql(): string {
        // TODO: Implement sql() method.
    }

    public function args(): array {
        // TODO: Implement args() method.
    }
}