<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\DataProcessing\Pagination;

class Pager implements \Iterator, \Countable {
    protected $items = [];

    /**
     * @var int
     */
    protected $pageSize = 20;

    /**
     * @var int
     */
    protected $currentPageNumber = 1;

    /**
     * @var bool
     */
    private $isValid = false;

    /**
     * @var ?int
     */
    private $totalItemsCount;

    public function setCurrentPageNumber(int $pageNumber): void {
        $pageNumber = \intval($pageNumber);
        $totalPagesCount = $this->totalPagesCount();
        if ($pageNumber > $totalPagesCount) {
            $pageNumber = $totalPagesCount;
        } elseif ($pageNumber < 1) {
            $pageNumber = 1;
        }
        $this->currentPageNumber = $pageNumber;
    }

    public function currentPageNumber(): int {
        return $this->currentPageNumber;
    }

    public function setItems(array $items): void {
        $this->items = $items;
        $this->totalItemsCount = null;
    }

    public function totalPagesCount(): int {
        return (int)\ceil($this->totalItemsCount() / $this->pageSize());
    }

    public function setPageSize(int $pageSize): void {
        $this->pageSize = \max(\intval($pageSize), 1);
        $this->totalItemsCount = null;
    }

    public function pageSize(): int {
        return $this->pageSize;
    }

    public function currentPage(): iterable {
        return $this->newPageByNumber($this->currentPageNumber());
    }

    public function newPageByNumber(int $pageNumber): Page {
        $pageNumber = \max(\intval($pageNumber), 1);
        $pageSize = $this->pageSize();
        $offset = ($pageNumber - 1) * $pageSize;
        return $this->newPage(
            $this->items(
                $offset,
                $pageSize
            )
        );
    }

    public function totalItemsCount(): int {
        if (null === $this->totalItemsCount) {
            $this->totalItemsCount = $this->calculateTotalItemsCount();
        }
        return $this->totalItemsCount;
    }

    public function rewind(): void {
        $this->isValid = true;
        $this->setCurrentPageNumber(1);
    }

    public function current(): Page {
        return $this->newPageByNumber($this->currentPageNumber());
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

    protected function calculateTotalItemsCount(): int {
        return \count($this->items);
    }

    /**
     * Creates a new Page with $items.
     */
    protected function newPage(array $items): Page {
        return new Page($items);
    }

    protected function items(int $offset, int $pageSize): array {
        return \array_slice($this->items, $offset, $pageSize);
    }
}
