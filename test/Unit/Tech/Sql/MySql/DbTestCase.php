<?php declare(strict_types=1);
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\IDbClient;
use Morpho\Testing\DbTestCase as BaseDbTestCase;
use PDO;

use function Morpho\Tech\Sql\mkDbClient;

abstract class DbTestCase extends BaseDbTestCase {
    protected IDbClient $db;
    protected PDO $pdo;

    public function setUp(): void {
        parent::setUp();
        $this->pdo = $this->mkPdo();
        $this->db = mkDbClient($this->pdo);
        foreach ($this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $tableName) {
            $this->pdo->exec('DROP TABLE `' . $tableName . '`');
        }
        $this->createFixtures($this->db);
    }

    protected function createCarsTable(bool $addData): void {
        $this->pdo->query('DROP TABLE IF EXISTS cars');
        $this->pdo->query(
            "CREATE TABLE cars (
            name varchar(20),
            color varchar(20),
            country varchar(20),
            type1 int,
            type2 enum('US', 'Japan', 'EU')
        )"
        );
        if ($addData) {
            $rows = [
                ['name' => "Chevrolet Camaro", 'color' => 'red', 'country' => 'US', 'type1' => 1, 'type2' => 'US'],
                ['name' => 'Mazda 6', 'color' => 'green', 'country' => 'JP', 'type1' => 2, 'type2' => 'Japan'],
                ['name' => 'Mazda CX-3', 'color' => 'green', 'country' => 'JP', 'type1' => 2, 'type2' => 'EU'],
            ];
            foreach ($rows as $row) {
                $sql = 'INSERT INTO cars (name, color, country, type1, type2) VALUES (:name, :color, :country, :type1, :type2)';
                $this->pdo->prepare($sql)->execute($row);
            }
        }
    }

    protected function assertSqlEquals(string $expectedSql, string $actualSql): void {
        $normalize = fn($sql) => preg_replace('~\s+~s', ' ', $sql);
        $this->assertSame($normalize($expectedSql), $normalize($actualSql));
    }
}