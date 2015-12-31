<?php
namespace Morpho\DataProcessing;

use Morpho\Di\IServiceManager;
use Morpho\Di\IServiceManagerAware;

abstract class DbPager extends Pager implements IServiceManagerAware {
    protected $serviceManager;

    protected $db;

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    protected function getItemList($offset, $pageSize) {
        $offset = intval($offset);
        $pageSize = intval($pageSize);
        return $this->getDb()->selectRows('* FROM (' . $this->getSqlQuery() . ") AS t LIMIT $offset, $pageSize");
    }

    protected function calculateTotalItemsCount() {
        return $this->getDb()->selectCell('COUNT(*) FROM (' . $this->getSqlQuery() . ') AS t');
    }

    protected function getDb() {
        if (null === $this->db) {
            return $this->serviceManager->get('db');
        }
        return $this->db;
    }

    abstract protected function getSqlQuery();
}
