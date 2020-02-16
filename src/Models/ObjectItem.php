<?php

namespace Rockschtar\WordPress\ObjectStorage\Models;

use Cassandra\Date;

class ObjectItem implements \JsonSerializable {

    /**
     * @var string
     */
    private $name;

    /**
     * @var int|null
     */
    private $expireTimestamp;

    /**
     * @var mixed
     */
    private $value;

    /**
     * ObjectItem constructor.
     * @param string $name
     * @param mixed $value
     * @param int|null $expireTimestamp
     */
    public function __construct(string $name, $value, ?int $expireTimestamp = null) {
        $this->name = $name;

        if ($expireTimestamp === 0) {
            $expireTimestamp = null;
        }

        $this->expireTimestamp = $expireTimestamp;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ObjectItem
     */
    public function setName(string $name): ObjectItem {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getExpireTimestamp(): ?int {
        return $this->expireTimestamp;
    }

    /**
     * @param int|null $expireTimestamp
     * @return ObjectItem
     */
    public function setExpireTimestamp(?int $expireTimestamp): ObjectItem {
        $this->expireTimestamp = $expireTimestamp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return ObjectItem
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    public function getExpireDateTime(): ?\DateTime {
        if ($this->getExpireTimestamp() === null) {
            return null;
        }

        $dateTime = new \DateTime();
        $dateTime->setTimezone(wp_timezone());
        $dateTime->setTimestamp($this->getExpireTimestamp());
        return $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize() {
        $expireDateTime = __('Never', 'rsos');

        if ($this->getExpireDateTime()) {
            $datetimeFormat = get_option('date_format');
            $datetimeFormat .= ' ' . get_option('time_format');
            $expireDateTime = $this->getExpireDateTime()->format($datetimeFormat);
        }

        return [
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'expireTimestamp' => $this->getExpireTimestamp(),
            'expireDateTime' => $expireDateTime
        ];
    }
}