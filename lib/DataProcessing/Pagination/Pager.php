<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\DataProcessing\Pagination;

use Morpho\Base\Node;
use function Morpho\Base\uniqueName;

class Pager extends Node {
    protected $items = [];

    protected $pageSize = 20;

    protected $currentPageNumber = 1;

    private $isValid = false;

    private $totalItemsCount;

    public function name(): string {
        if (null === $this->name) {
            $this->name = uniqueName();
        }
        return $this->name;
    }

    public function setCurrentPageNumber($pageNumber) {
        $pageNumber = intval($pageNumber);
        $totalPagesCount = $this->totalPagesCount();
        if ($pageNumber > $totalPagesCount) {
            $pageNumber = $totalPagesCount;
        } elseif ($pageNumber < 1) {
            $pageNumber = 1;
        }
        $this->currentPageNumber = $pageNumber;
        return $this;
    }

    public function currentPageNumber() {
        return $this->currentPageNumber;
    }

    public function setItems(array $items) {
        $this->items = $items;
        $this->totalItemsCount = null;

        return $this;
    }

    public function totalPagesCount() {
        return (int)ceil($this->totalItemsCount() / $this->pageSize());
    }

    public function setPageSize($pageSize) {
        $this->pageSize = max(intval($pageSize), 1);
        $this->totalItemsCount = null;

        return $this;
    }

    public function pageSize() {
        return $this->pageSize;
    }

    public function currentPage() {
        return $this->page($this->currentPageNumber());
    }

    /**
     * {@inheritdoc}
     */
    public function page($pageNumber): iterable {
        $pageNumber = max(intval($pageNumber), 1);
        $pageSize = $this->pageSize();
        $offset = ($pageNumber - 1) * $pageSize;
        return $this->newPage(
            $this->items(
                $offset,
                $pageSize
            )
        );
    }

    public function totalItemsCount() {
        if (null === $this->totalItemsCount) {
            $this->totalItemsCount = $this->calculateTotalItemsCount();
        }

        return $this->totalItemsCount;
    }

    public function rewind(): void {
        $this->isValid = true;
        $this->setCurrentPageNumber(1);
    }

    /**
     * @return array
     */
    public function current() {
        return $this->page($this->currentPageNumber());
    }

    public function valid(): bool {
        return $this->isValid && $this->currentPageNumber() <= $this->totalPagesCount();
    }

    /**
     * @return string int float
     */
    public function key() {
        return $this->currentPageNumber();
    }

    public function next(): void {
        $nextPageNumber = $this->currentPageNumber() + 1;
        if ($nextPageNumber > $this->totalPagesCount()) {
            $this->isValid = false;
        } else {
            $this->setCurrentPageNumber($this->currentPageNumber() + 1);
        }
    }

    public function count(): int {
        return $this->totalPagesCount();
    }

    protected function calculateTotalItemsCount() {
        return count($this->items);
    }

    /**
     * Creates a new Page with $items.
     */
    protected function newPage(array $items): iterable {
        return new Page($items);
    }

    /**
     * @return array
     */
    protected function items($offset, $pageSize) {
        return array_slice($this->items, $offset, $pageSize);
    }
}
