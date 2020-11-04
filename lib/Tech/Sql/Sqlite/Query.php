<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\Sqlite;

use Morpho\Base\NotImplementedException;
use Morpho\Tech\Sql\Query as BaseQuery;

class Query extends BaseQuery {
    public function identifier(string $identifier): string {
        return '"' . $identifier . '"';
    }

    public function build(): array {
        throw new NotImplementedException();
    }
}