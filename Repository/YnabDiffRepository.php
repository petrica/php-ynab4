<?php

namespace MTools\Ynab\Repository;

use MTools\Ynab\Entity\YnabDevice;
use MTools\Ynab\Entity\YnabDiff;
use MTools\Ynab\Entity\YnabEntities;
use MTools\Ynab\Entity\YnabFile;
use MTools\Ynab\Entity\YnabTransaction;
use MTools\Ynab\Entity\YnabVersion;
use MTools\Ynab\IO\YnabIOInterface;
use MTools\Ynab\Parser\YnabDiffVersionParser;
use MTools\Ynab\Parser\YnabTransactionParser;
use MTools\Ynab\Parser\YnabVersionParser;
use MTools\Ynab\Utils\YnabVersionUtils;

class YnabDiffRepository implements YnabRepositoryInterface
{
    /**
     * @var YnabDevice[]
     */
    protected $devices;

    /**
     * @var YnabVersion[]
     */
    protected $knowledge;

    /**
     * @var YnabIOInterface
     */
    protected $io;

    /**
     * YnabDiffRepository constructor.
     *
     * @param $device YnabDevice[]
     * @param $knowledge YnabVersion[]
     * @param $io YnabIOInterface
     */
    public function __construct($devices, $knowledge, $io)
    {
        $this->devices = $devices;
        $this->knowledge = $knowledge;
        $this->io = $io;
    }

    /**
     * Get diffs entities for current knowledge
     */
    public function read()
    {
        $files = [];
        foreach ($this->devices as $device) {
            $files = array_merge($files, $this->getDeviceFiles($device));
        }

        $transactions = [];
        foreach ($files as $file) {
            $data = $this->io->read($file);

            $items = $data->getContent()->items;

            $versionParser = new YnabVersionParser($data->getContent()->endVersion);
            $endVersion = $versionParser->parse();

            $utils = new YnabVersionUtils();
            $this->knowledge = $utils->mergeKnowledge($endVersion, $this->knowledge);

            $transactions = array_merge($transactions, $this->parseTransactions($items));
        }

        $entities = new YnabEntities();
        $entities->setTransactions($transactions);
        $entities->setKnowledge($this->knowledge);

        return $entities;
    }

    /**
     * Write a new diff
     *
     * @param YnabDiff $entity
     * @return bool
     */
    public function write($entity)
    {
        if (!isset($this->devices[$entity->getShortDeviceId()])) {
            throw new \RuntimeException(sprintf('Could not find device with id %d', $entity->getShortDeviceId()));
        }

        $utils = new YnabVersionUtils();
        $to = $entity->getEndVersion();

        $device = $this->devices[$entity->getShortDeviceId()];
        $path = $device->getDataFullPath() . '/' . $utils->packKnowledge($entity->getStartVersion()) .
            '_' .
            $to[$device->getShortDeviceId()]->__toString() .
            '.ydiff';

        $file = new YnabFile($path, json_encode($entity));
        return $this->io->write($file);
    }

    /**
     * Device files to identify
     *
     * @param $device YnabDevice
     * @return $path string
     */
    protected function getDeviceFiles($device)
    {
        $files = $this->io->ls($device->getDataFullPath());

        $return = [];
        foreach ($files as $file) {
            $info = pathinfo($file);

            if ($info['extension'] == 'ydiff') {
                $name = $info['filename'];

                $parser = new YnabDiffVersionParser($name);
                $diffVersion = $parser->parse();

                // Determine if diff is valid
                $isDiff = false;
                if (isset($this->knowledge[$device->getShortDeviceId()])) {
                    /**
                     * Is a new version for this device version?
                     */
                    if ($diffVersion->getTo()->getIncrement() > $this->knowledge[$device->getShortDeviceId()]->getIncrement()) {
                        $isDiff = true;
                    }
                }
                else {
                    // This version has never been ingested
                    $isDiff = true;
                }

                if ($isDiff) {
                    $return[] = $file;
                }
            }
        }

        return $return;
    }

    /**
     * Parse transactions from items
     *
     * @param $items
     * @return YnabTransaction[]
     */
    protected function parseTransactions($items)
    {
        $transactions = [];
        foreach ($items as $item) {
            if ($item->entityType == 'transaction') {
                $parser = new YnabTransactionParser($item);
                $transactions[] = $parser->parse();
            }
        }

        return $transactions;
    }
}