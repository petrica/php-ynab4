<?php

namespace Petrica\Ynab\Entity;

/**
 * Class YnabBudget
 *
 * @package Petrica\Ynab\Entity
 */
class YnabBudget
{
    private $formatVersion;
    private $relativeDataFolderName;
    private $fullDataPath;
    private $TED;

    /**
     * @return mixed
     */
    public function getFormatVersion()
    {
        return $this->formatVersion;
    }

    /**
     * @param mixed $formatVersion
     */
    public function setFormatVersion($formatVersion)
    {
        $this->formatVersion = $formatVersion;
    }

    /**
     * @return mixed
     */
    public function getRelativeDataFolderName()
    {
        return $this->relativeDataFolderName;
    }

    /**
     * @param mixed $relativeDataFolderName
     */
    public function setRelativeDataFolderName($relativeDataFolderName)
    {
        $this->relativeDataFolderName = $relativeDataFolderName;
    }

    /**
     * @return mixed
     */
    public function getTED()
    {
        return $this->TED;
    }

    /**
     * @param mixed $TED
     */
    public function setTED($TED)
    {
        $this->TED = $TED;
    }

    /**
     * @return mixed
     */
    public function getFullDataPath()
    {
        return $this->fullDataPath;
    }

    /**
     * @param mixed $fullDataPath
     */
    public function setFullDataPath($fullDataPath)
    {
        $this->fullDataPath = $fullDataPath;
    }
}