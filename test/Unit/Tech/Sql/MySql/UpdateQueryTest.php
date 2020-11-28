<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\Result;

class UpdateQueryTest extends DbTestCase {
    public function setUp(): void {
        parent::setUp();
        $this->createCarsTable(true);
    }

    public function testQuery() {
        $modelName = 'Chevrolet Camaro';

        $selectRows = function () use ($modelName) {
            $stmt = $this->pdo->prepare('SELECT * FROM cars WHERE name = ?');
            $stmt->execute([$modelName]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        };

        $rows = $selectRows();
        $this->assertCount(1, $rows);
        $oldColor = 'red';
        $this->assertSame($oldColor, $rows[0]['color']);

        $newColor = 'white metallic';
        $query = $this->db->update()->table('cars')->columns(['color' => $newColor])->where('name = ?', [$modelName]);

        $result = $query->eval();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertSame(1, $result->rowCount());

        $this->assertSame([
            ['name' => $modelName, 'color' => $newColor, 'country' => 'US', 'type1' => 1, 'type2' => 'US'],
        ], $selectRows());
    }
}
