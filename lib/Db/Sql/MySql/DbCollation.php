<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

class DbCollation extends Collation {
    /**
     * @var string
     */
    private $dbName;

    public function __construct(string $name, string $charsetName, string $dbName) {
        parent::__construct($name, $charsetName);
        $this->dbName = $dbName;
    }

    public function dbName(): string {
        return $this->dbName;
    }
}