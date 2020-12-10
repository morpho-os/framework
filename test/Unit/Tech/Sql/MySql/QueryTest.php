<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\IQuery;

abstract class QueryTest extends DbTestCase {
    protected IQuery $query;

    public function setUp(): void {
        parent::setUp();
        $this->query = $this->mkQuery();
    }

    protected function checkTableRef() {
        $sql = $this->query->table(['abc' => 'someAlias', 'interTable', 'def' => 'anotherAlias'])->__toString();
        $this->assertStringContainsString('`abc` AS `someAlias`, `interTable`, `def` AS `anotherAlias`', $sql);
    }

    abstract protected function mkQuery(): IQuery;
}