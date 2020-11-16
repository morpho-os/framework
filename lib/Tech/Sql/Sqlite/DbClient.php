<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql\Sqlite;

use Morpho\Base\NotImplementedException;
use Morpho\Tech\Sql\DbClient as BaseDbClient;

class DbClient extends BaseDbClient {
    protected function connect($confOrPdo): \PDO {
        // TODO: Implement connect() method.
        throw new NotImplementedException(__METHOD__);
    }
}