<?php

namespace Petrica\Ynab\Diff;

use Petrica\Ynab\Entity\YnabVersion;

class YnabDiff
{
    /**
     * @var YnabVersion[]
     */
    protected $from;
    /**
     * @var YnabVersion
     */
    protected $to;
    protected $path;

    /**
     * YnabDiff constructor.
     * @param $path
     * @param $from
     * @param $to
     */
    public function __construct($path, $from, $to)
    {
        $this->path = $path;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Tree graph diff distance based on version
     */
    public function getDistance()
    {
        $dist = 0;
        return array_reduce($this->from, function ($sum, $item) {
            $sum += $item->getIncrement();
            return $sum;
        });
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
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
     * @param YnabVersion $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}