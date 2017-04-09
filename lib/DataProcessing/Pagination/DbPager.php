<?php
namespace Morpho\DataProcessing\Pagination;

use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

abstract class DbPager extends Pager implements IServiceManagerAware {
    protected $serviceManager;

    protected $db;

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    protected function itemList($offset, $pageSize): iterable {
        $offset = intval($offset);
        $pageSize = intval($pageSize);
        return $this->db()->select('* FROM (' . $this->sqlQuery() . ") AS t LIMIT $offset, $pageSize");
    }

    protected function calculateTotalItemsCount() {
        return $this->db()->select('COUNT(*) FROM (' . $this->sqlQuery() . ') AS t')->cell();
    }

    protected function db() {
        if (null === $this->db) {
            return $this->serviceManager->get('db');
        }
        return $this->db;
    }

    abstract protected function sqlQuery();
}
