<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Test\Unit\Tech\Sql\MySql;

use Morpho\Tech\Sql\IQuery;
use Morpho\Tech\Sql\MySql\ReplaceQuery;

class ReplaceQueryTest extends QueryTest {
    public function testQuery() {
        $this->markTestIncomplete();
    }

    protected function mkQuery(): IQuery {
        return new ReplaceQuery($this->db);
    }
}
