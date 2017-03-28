<?php

namespace Petrica\Ynab\Entity;

/**
 * Class YnabDiffVersion
 *
 * @package Petrica\Ynab\Entity
 */
class YnabDiffVersion
{
    /**
     * @var YnabVersion[]
     */
    private $from;

    /**
     * @var YnabVersion
     */
    private $to;

    /**
     * @return YnabVersion[]
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param YnabVersion[] $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return YnabVersion
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param YnabVersion[] $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * Tree graph diff distance based on version
     */
    public function getDistance()
    {
        return array_reduce($this->from, function ($sum, $item) {
            $sum += $item->getIncrement();
            return $sum;
        });
    }
}