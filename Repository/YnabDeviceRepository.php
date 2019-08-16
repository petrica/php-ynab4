<?php

namespace Petrica\Ynab\Repository;
use Petrica\Ynab\Entity\YnabBudget;
use Petrica\Ynab\Entity\YnabDevice;
use Petrica\Ynab\IO\YnabIOInterface;
use Petrica\Ynab\Parser\YnabVersionParser;
use Petrica\Ynab\Entity\YnabFile;

/**
 * Class YnabDevicesParser
 *
 * @package Petrica\Ynab\Entity
 */
class YnabDeviceRepository implements YnabRepositoryInterface
{
    /**
     * @var YnabBudget
     */
    protected $budget;

    /**
     * @var YnabIOInterface
     */
    protected $io;

    /**
     * @var string
     */
    protected $devicesPath;

    /**
     * YnabDevicesParser constructor.
     *
     * @param $budget
     * @param $io
     */
    public function __construct($budget, $io)
    {
        $this->budget = $budget;
        $this->io = $io;
        $this->devicesPath = $this->budget->getFullDataPath() . '/devices';
    }

    /**
     * Parse available devices
     *
     * @return YnabDevice[]
     */
    public function read()
    {
        $devices = [];
        $filenames = $this->getDevicesFilename();

        if (count($filenames)) {
            foreach ($filenames as $path) {
                $data = $this->io->read($path);

                $device = new YnabDevice();
                $device->setFriendlyName($data->getContent()->friendlyName);

                $knowledgeParser = new YnabVersionParser($data->getContent()->knowledgeInFullBudgetFile);
                $device->setKnowledgeInFullBudgetFile($knowledgeParser->parse());
                $device->setYNABVersion($data->getContent()->YNABVersion);
                $device->setLastDataVersionFullyKnown($data->getContent()->lastDataVersionFullyKnown);
                $device->setDeviceType($data->getContent()->deviceType);

                $knowledgeParser = new YnabVersionParser($data->getContent()->knowledge);
                $device->setKnowledge($knowledgeParser->parse());
                $device->setHighestDataVersionImported($data->getContent()->highestDataVersionImported);
                $device->setShortDeviceId($data->getContent()->shortDeviceId);
                $device->setFormatVersion($data->getContent()->formatVersion);
                $device->setHasFullKnowledge($data->getContent()->hasFullKnowledge);
                $device->setDeviceGUID($data->getContent()->deviceGUID);
                $device->setDataFullPath($this->budget->getFullDataPath() . '/' . $device->getDeviceGUID());

                $devices[$device->getShortDeviceId()] = $device;
            }
        }
        else {
            throw new \RuntimeException('Could not find any devices to work with. Do you have a valid YNAB budget?');
        }

        return $devices;
    }

    /**
     * Write device to YNAB4 database
     *
     * @param YnabDevice $entity
     * @return mixed
     */
    public function write($entity)
    {
        $file = new YnabFile(
            $this->devicesPath . '/' . $entity->getShortDeviceId() . '.ydevice',
            json_encode($entity)
        );

        return $this->io->write($file);
    }


    /**
     * Return available devices filenames
     *
     * @return array
     */
    protected function getDevicesFilename()
    {
        $files = $this->io->ls($this->devicesPath);

        $names = [];
        foreach ($files as $file) {
            $info = pathinfo($file);
            if (isset($info['filename'])) {
                $names[] = $file;
            }
        }

        return $names;
    }
}