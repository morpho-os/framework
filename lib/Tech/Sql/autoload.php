<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Sql;

use PDO;
use UnexpectedValueException;

const SQL_TRUE = 1;
const SQL_FALSE = 0;

function mkDbClient($confOrPdo = null): IDbClient {
    if ($confOrPdo instanceof PDO) {
        $driverName = $confOrPdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    } else {
        $confOrPdo = (array) $confOrPdo;
        $driverName = $confOrPdo['driver'] ?? 'mysql';
        unset($confOrPdo['driver']);
    }
    switch ($driverName) {
        case 'mysql':
            return new MySql\DbClient($confOrPdo);
        case 'sqlite':
            return new Sqlite\DbClient($confOrPdo);
    }
    throw new UnexpectedValueException("Unknown DB driver");
}