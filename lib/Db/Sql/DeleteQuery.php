<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types=1);
namespace Morpho\Db\Sql;

use Morpho\Base\NotImplementedException;

class DeleteQuery extends Query {
    public function build(): array {
        throw new NotImplementedException();
    }
}