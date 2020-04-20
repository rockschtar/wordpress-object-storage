<?php

namespace Rockschtar\WordPress\ObjectStorage\Models;

class ObjectStorageItem {

    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed|null
     */
    private $value;

    /**
     * @var int|null
     */
    private $expiresAtTimestamp;

    /**
     * @var \DateTime|null
     */
    private $expiresAtDateTime;

    /**
     * ObjectStorageItem constructor.
     * @param string $key
     */
    public function __construct(string $key) {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey(): string {
        return $this->key;
    }

    /**
     * @param string $key
     * @return ObjectStorageItem
     */
    public function setKey(string $key): ObjectStorageItem {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getValue(): ?mixed {
        return $this->value;
    }

    /**
     * @param mixed|null $value
     * @return ObjectStorageItem
     */
    public function setValue(?mixed $value): ObjectStorageItem {
        $this->value = $value;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getExpiresAtTimestamp(): ?int {
        return $this->expiresAtTimestamp;
    }

    /**
     * @param int|null $expiresAtTimestamp
     * @return ObjectStorageItem
     */
    public function setExpiresAtTimestamp(?int $expiresAtTimestamp): ObjectStorageItem {
        $this->expiresAtTimestamp = $expiresAtTimestamp;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiresAtDateTime(): ?\DateTime {
        return $this->expiresAtDateTime;
    }

    /**
     * @param \DateTime|null $expiresAtDateTime
     * @return ObjectStorageItem
     */
    public function setExpiresAtDateTime(?\DateTime $expiresAtDateTime): ObjectStorageItem {
        $this->expiresAtDateTime = $expiresAtDateTime;
        return $this;
    }

}