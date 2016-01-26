<?php
namespace Morpho\DataProcessing\Pagination;

use Morpho\Base\Node;
use function Morpho\Base\uniqueName;

class Pager extends Node {
    protected $items = [];

    protected $pageSize = 20;

    protected $currentPageNumber = 1;

    private $isValid = false;

    private $totalItemsCount;

    public function getName(): string {
        if (null === $this->name) {
            $this->name = uniqueName();
        }
        return $this->name;
    }

    public function setCurrentPageNumber($pageNumber) {
        $pageNumber = intval($pageNumber);
        $totalPagesCount = $this->getTotalPagesCount();
        if ($pageNumber > $totalPagesCount) {
            $pageNumber = $totalPagesCount;
        } elseif ($pageNumber < 1) {
            $pageNumber = 1;
        }
        $this->currentPageNumber = $pageNumber;
        return $this;
    }

    public function getCurrentPageNumber() {
        return $this->currentPageNumber;
    }

    public function setItems(array $items) {
        $this->items = $items;
        $this->totalItemsCount = null;

        return $this;
    }

    public function getTotalPagesCount() {
        return (int)ceil($this->getTotalItemsCount() / $this->getPageSize());
    }

    public function setPageSize($pageSize) {
        $this->pageSize = max(intval($pageSize), 1);
        $this->totalItemsCount = null;

        return $this;
    }

    public function getPageSize() {
        return $this->pageSize;
    }

    public function getCurrentPage() {
        return $this->getPage($this->getCurrentPageNumber());
    }

    /**
     * {@inheritdoc}
     */
    public function getPage($pageNumber) {
        $pageNumber = max(intval($pageNumber), 1);
        $pageSize = $this->getPageSize();
        $offset = ($pageNumber - 1) * $pageSize;
        return $this->createPage(
            $this->getItemList(
                $offset,
                $pageSize
            )
        );
    }

    public function getTotalItemsCount() {
        if (null === $this->totalItemsCount) {
            $this->totalItemsCount = $this->calculateTotalItemsCount();
        }

        return $this->totalItemsCount;
    }

    /**
     * @return void
     */
    public function rewind() {
        $this->isValid = true;
        $this->setCurrentPageNumber(1);
    }

    /**
     * @return array
     */
    public function current() {
        return $this->getPage($this->getCurrentPageNumber());
    }

    /**
     * @return bool
     */
    public function valid() {
        return $this->isValid && $this->getCurrentPageNumber() <= $this->getTotalPagesCount();
    }

    /**
     * @return string int float
     */
    public function key() {
        return $this->getCurrentPageNumber();
    }

    /**
     * @return void
     */
    public function next() {
        $nextPageNumber = $this->getCurrentPageNumber() + 1;
        if ($nextPageNumber > $this->getTotalPagesCount()) {
            $this->isValid = false;
        } else {
            $this->setCurrentPageNumber($this->getCurrentPageNumber() + 1);
        }
    }

    /**
     * @return int
     */
    public function count() {
        return $this->getTotalPagesCount();
    }

    protected function calculateTotalItemsCount() {
        return count($this->items);
    }

    /**
     * Creates a new Page with $items.
     */
    protected function createPage(array $items) {
        return new Page($items);
    }

    /**
     * @return array
     */
    protected function getItemList($offset, $pageSize) {
        return array_slice($this->items, $offset, $pageSize);
    }
}
