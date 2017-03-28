<?php

namespace Petrica\Ynab\Entity;

/**
 * Class YnabDiff
 * @package Petrica\Ynab\Entity
 */
class YnabDiff
{
    private $startVersion;
    private $endVersion;
    private $formatVersion = null;
    private $publishTime;
    private $deviceGUID;
    private $shortDeviceId;
    private $dataVersion = '4.2';
    private $budgetDataGUID;
    /**
     * @var mixed[]
     */
    private $items;

    /**
     * @return YnabVersion[]
     */
    public function getStartVersion()
    {
        return $this->startVersion;
    }

    /**
     * @param mixed $startVersion
     */
    public function setStartVersion($startVersion)
    {
        $this->startVersion = $startVersion;
    }

    /**
     * @return YnabVersion[]
     */
    public function getEndVersion()
    {
        return $this->endVersion;
    }

    /**
     * @param mixed $endVersion
     */
    public function setEndVersion($endVersion)
    {
        $this->endVersion = $endVersion;
    }

    /**
     * @return null
     */
    public function getFormatVersion()
    {
        return $this->formatVersion;
    }

    /**
     * @param null $formatVersion
     */
    public function setFormatVersion($formatVersion)
    {
        $this->formatVersion = $formatVersion;
    }

    /**
     * @return mixed
     */
    public function getPublishTime()
    {
        return $this->publishTime;
    }

    /**
     * @param mixed $publishTime
     */
    public function setPublishTime($publishTime)
    {
        $this->publishTime = $publishTime;
    }

    /**
     * @return mixed
     */
    public function getDeviceGUID()
    {
        return $this->deviceGUID;
    }

    /**
     * @param mixed $deviceGUID
     */
    public function setDeviceGUID($deviceGUID)
    {
        $this->deviceGUID = $deviceGUID;
    }

    /**
     * @return mixed
     */
    public function getShortDeviceId()
    {
        return $this->shortDeviceId;
    }

    /**
     * @param mixed $shortDeviceId
     */
    public function setShortDeviceId($shortDeviceId)
    {
        $this->shortDeviceId = $shortDeviceId;
    }

    /**
     * @return string
     */
    public function getDataVersion()
    {
        return $this->dataVersion;
    }

    /**
     * @param string $dataVersion
     */
    public function setDataVersion($dataVersion)
    {
        $this->dataVersion = $dataVersion;
    }

    /**
     * @return mixed
     */
    public function getBudgetDataGUID()
    {
        return $this->budgetDataGUID;
    }

    /**
     * @param mixed $budgetDataGUID
     */
    public function setBudgetDataGUID($budgetDataGUID)
    {
        $this->budgetDataGUID = $budgetDataGUID;
    }

    /**
     * @return mixed[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
}