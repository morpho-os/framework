<?php declare(strict_types=1);
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