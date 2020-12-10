<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\IQuery;
use Morpho\Tech\Sql\MySql\SelectQuery;
use UnexpectedValueException;

class SelectQueryTest extends QueryTest {
    use TUsingNorthwind;

    public function setUp(): void {
        parent::setUp();
        $this->query = new SelectQuery($this->db);
    }

    public function testWithoutTable() {
        $columns = "MICROSECOND('2019-12-31 23:59:59.000010'), NOW()";
        $this->assertSqlEquals("SELECT " . $columns, (string) $this->query->columns($this->query->expr($columns)));
    }

    public function testWithTable() {
        $this->assertSqlEquals("SELECT * FROM `cars`", (string) $this->query->table('cars'));
    }

    public function testCompleteSelect() {
        $this->query->columns(['customers.id'])
            ->table(
                $this->query->expr('customers INNER JOIN orders ON customers.id = orders.customer_id')
            );
        $this->assertSqlEquals('SELECT `customers`.`id` FROM customers INNER JOIN orders ON customers.id = orders.customer_id', $this->query);
            /*
            todo
            ->where()
            ->groupBy()
            ->having()
            ->window()
            ->orderBy()
            ->limit()
            ->offset()
            ->for()
            ->__toString();
            */
    }

    public function dataForJoin() {
        yield ['INNER'];
        yield ['LEFT'];
        yield ['RIGHT'];
    }

    /**
     * @dataProvider dataForJoin
     */
    public function testJoin($joinType) {
        $columns = 'task AS t.*, tL.startedAt, tL.endedAt, tL.exitCode';
        $join = 'taskLaunch tL ON t.id = tL.taskId';
        $query = $this->query->table(['task', 'taskLaunch'])
            ->columns($this->query->expr($columns));
        $joinToMethod = [
            'INNER' => 'innerJoin',
            'LEFT' => 'leftJoin',
            'RIGHT' => 'rightJoin',
        ];
        $method = $joinToMethod[$joinType];
        $sql = $query->$method($join)
            ->where(['foo' => 'bar'])
            ->__toString();
        $this->assertSqlEquals("SELECT $columns FROM `task`, `taskLaunch` $joinType JOIN $join WHERE `foo` = 'bar'", $sql);
    }

    public function testWhereClause_OnlyCondition_ValidArg() {
        $this->assertSqlEquals("SELECT * WHERE `foo` = 'abc' AND `bar` = 'efg'", $this->query->where(['foo' => 'abc', 'bar' => 'efg']));
    }

    public function testWhereClause_OnlyCondition_InvalidArg() {
        $this->expectException(UnexpectedValueException::class);
        $this->query->where(['foo', 'bar']);
    }

    public function testTableRef() {
        $this->checkTableRef();
    }

    protected function mkQuery(): IQuery {
        return new SelectQuery($this->db);
    }
}
