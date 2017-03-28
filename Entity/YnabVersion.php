<?php

namespace Petrica\Ynab\Entity;

class YnabVersion
{
    protected $shortId;
    protected $increment;

    public function __construct($shortId, $increment)
    {
        $this->shortId = $shortId;
        $this->increment = $increment;
    }

    /**
     * @return mixed
     */
    public function getShortId()
    {
        return $this->shortId;
    }

    /**
     * @param mixed $shortId
     */
    public function setShortId($shortId)
    {
        $this->shortId = $shortId;
    }

    /**
     * @return mixed
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * @param mixed $increment
     */
    public function setIncrement($increment)
    {
        $this->increment = $increment;
    }

    /**
     * Increment version by amount
     *
     * @param int $amount
     */
    public function increment($amount = 1)
    {
        $this->setIncrement($this->getIncrement() + $amount);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getShortId() . '-' . $this->getIncrement();
    }
}