<?php

namespace Rockschtar\WordPress\ObjectStorage\Models;

class ObjectItems implements \JsonSerializable {

    /**
     * @var ObjectItem[]
     */
    private $items = [];

    /**
     * @var
     */
    private $totalPages;

    /**
     * @var
     */
    private $currentPage;

    /**
     * @var
     */
    private $pageSize;

    public function addItem(ObjectItem $objectItem): void {
        $this->items[] = $objectItem;
    }

    public function jsonSerialize() {
        return [
            'items' => $this->items
        ];
    }
}