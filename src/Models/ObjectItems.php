<?php

namespace Rockschtar\WordPress\ObjectStorage\Models;

class ObjectItems implements \JsonSerializable {

    /**
     * @var ObjectItem[]
     */
    private $items = [];

    /**
     * @var int
     */
    private $totalPages;

    /**
     * @var int
     */
    private $totalItems;

    /**
     * @var
     */
    private $currentPage;

    /**
     * @var int
     */
    private $pageSize;

    public function addItem(ObjectItem $objectItem): void {
        $this->items[] = $objectItem;
    }

    /**
     * @return ObjectItem[]
     */
    public function getItems(): array {
        return $this->items;
    }

    /**
     * @param ObjectItem[] $items
     * @return ObjectItems
     */
    public function setItems(array $items): ObjectItems {
        $this->items = $items;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int {
        return $this->totalPages;
    }

    /**
     * @param int $totalPages
     * @return ObjectItems
     */
    public function setTotalPages(int $totalPages): ObjectItems {
        $this->totalPages = $totalPages;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int {
        return $this->totalItems;
    }

    /**
     * @param int $totalItems
     * @return ObjectItems
     */
    public function setTotalItems(int $totalItems): ObjectItems {
        $this->totalItems = $totalItems;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }

    /**
     * @param mixed $currentPage
     * @return ObjectItems
     */
    public function setCurrentPage($currentPage) {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int {
        return $this->pageSize;
    }

    /**
     * @param int $pageSize
     * @return ObjectItems
     */
    public function setPageSize(int $pageSize): ObjectItems {
        $this->pageSize = $pageSize;
        return $this;
    }

    public function jsonSerialize() {
        return [
            'items' => $this->items,
            'currentPage' => $this->getCurrentPage(),
            'totalPages' => $this->getTotalPages(),
            'totalItems' => $this->getTotalItems()
        ];
    }
}