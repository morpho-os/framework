<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\DataProcessing\Pagination;

use Morpho\Ioc\IServiceManager;
use Morpho\Ioc\IHasServiceManager;

abstract class DbPager extends Pager implements IHasServiceManager {
    protected $serviceManager;

    protected $db;

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
        $this->db = null;
    }

    protected function itemList($offset, $pageSize): iterable {
        $offset = intval($offset);
        $pageSize = intval($pageSize);
        return $this->db()->select('* FROM (' . $this->sqlQuery() . ") AS t LIMIT $offset, $pageSize");
    }

    protected function calculateTotalItemsCount(): int {
        return $this->db()->select('COUNT(*) FROM (' . $this->sqlQuery() . ') AS t')->field();
    }

    protected function db() {
        if (null === $this->db) {
            return $this->serviceManager['db'];
        }
        return $this->db;
    }

    abstract protected function sqlQuery();
}
