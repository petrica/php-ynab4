<?php

namespace MTools\Ynab\Entity;
use MTools\Ynab\Utils\YnabVersionUtils;

/**
 * Class YnabDevice
 *
 * @package MTools\Ynab\Entity
 */
class YnabDevice implements \JsonSerializable
{
    private $YNABVersion;
    private $deviceGUID;
    private $deviceType;
    private $formatVersion;
    private $friendlyName;
    private $hasFullKnowledge;
    private $highestDataVersionImported;
    /**
     * @var YnabVersion[]
     */
    private $knowledge;
    /**
     * @var YnabVersion[]
     */
    private $knowledgeInFullBudgetFile;
    private $lastDataVersionFullyKnown;
    private $shortDeviceId;

    /**
     * @var string Full path to device data folder
     */
    protected $dataFullPath;

    /**
     * @return mixed
     */
    public function getYNABVersion()
    {
        return $this->YNABVersion;
    }

    /**
     * @param mixed $YNABVersion
     */
    public function setYNABVersion($YNABVersion)
    {
        $this->YNABVersion = $YNABVersion;
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
    public function getDeviceType()
    {
        return $this->deviceType;
    }

    /**
     * @param mixed $deviceType
     */
    public function setDeviceType($deviceType)
    {
        $this->deviceType = $deviceType;
    }

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
    public function getFriendlyName()
    {
        return $this->friendlyName;
    }

    /**
     * @param mixed $friendlyName
     */
    public function setFriendlyName($friendlyName)
    {
        $this->friendlyName = $friendlyName;
    }

    /**
     * @return mixed
     */
    public function getHasFullKnowledge()
    {
        return $this->hasFullKnowledge;
    }

    /**
     * @param mixed $hasFullKnowledge
     */
    public function setHasFullKnowledge($hasFullKnowledge)
    {
        $this->hasFullKnowledge = $hasFullKnowledge;
    }

    /**
     * @return mixed
     */
    public function getHighestDataVersionImported()
    {
        return $this->highestDataVersionImported;
    }

    /**
     * @param mixed $highestDataVersionImported
     */
    public function setHighestDataVersionImported($highestDataVersionImported)
    {
        $this->highestDataVersionImported = $highestDataVersionImported;
    }

    /**
     * @return YnabVersion[]
     */
    public function getKnowledge()
    {
        return $this->knowledge;
    }

    /**
     * @param mixed $knowledge
     */
    public function setKnowledge($knowledge)
    {
        $this->knowledge = $knowledge;
    }

    /**
     * @return mixed
     */
    public function getKnowledgeInFullBudgetFile()
    {
        return $this->knowledgeInFullBudgetFile;
    }

    /**
     * @param mixed $knowledgeInFullBudgetFile
     */
    public function setKnowledgeInFullBudgetFile($knowledgeInFullBudgetFile)
    {
        $this->knowledgeInFullBudgetFile = $knowledgeInFullBudgetFile;
    }

    /**
     * @return mixed
     */
    public function getLastDataVersionFullyKnown()
    {
        return $this->lastDataVersionFullyKnown;
    }

    /**
     * @param mixed $lastDataVersionFullyKnown
     */
    public function setLastDataVersionFullyKnown($lastDataVersionFullyKnown)
    {
        $this->lastDataVersionFullyKnown = $lastDataVersionFullyKnown;
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
    public function getDataFullPath()
    {
        return $this->dataFullPath;
    }

    /**
     * @param string $dataFullPath
     */
    public function setDataFullPath($dataFullPath)
    {
        $this->dataFullPath = $dataFullPath;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        $utils = new YnabVersionUtils();
        $data = get_object_vars($this);
        $data['knowledge'] = $utils->packKnowledge($this->getKnowledge());
        $data['knowledgeInFullBudgetFile'] = $utils->packKnowledge($this->getKnowledgeInFullBudgetFile());
        unset($data['dataFullPath']);
        return $data;
    }
}