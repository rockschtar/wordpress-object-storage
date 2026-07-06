<?php

namespace Rockschtar\WordPress\ObjectStorage\Models;

class ObjectStorageItem
{
    private string $key;

    private mixed $value = null;

    private ?int $expiresAtTimestamp = null;

    private ?\DateTime $expiresAtDateTime = null;

    public function __construct(string $key, mixed $value, ?int $expiresAtTimestamp = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->expiresAtTimestamp = $expiresAtTimestamp;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): ObjectStorageItem
    {
        $this->key = $key;
        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): ObjectStorageItem
    {
        $this->value = $value;
        return $this;
    }

    public function getExpiresAtTimestamp(): ?int
    {
        return $this->expiresAtTimestamp;
    }

    public function setExpiresAtTimestamp(?int $expiresAtTimestamp): ObjectStorageItem
    {
        $this->expiresAtTimestamp = $expiresAtTimestamp;
        return $this;
    }

    public function getExpiresAtDateTime(): ?\DateTime
    {
        return $this->expiresAtDateTime;
    }

    public function setExpiresAtDateTime(?\DateTime $expiresAtDateTime): ObjectStorageItem
    {
        $this->expiresAtDateTime = $expiresAtDateTime;
        return $this;
    }

}
