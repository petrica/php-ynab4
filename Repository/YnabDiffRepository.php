<?php

namespace Petrica\Ynab\Repository;

use Petrica\Ynab\Entity\YnabDevice;
use Petrica\Ynab\Entity\YnabDiff;
use Petrica\Ynab\Entity\YnabEntities;
use Petrica\Ynab\Entity\YnabFile;
use Petrica\Ynab\Entity\YnabTransaction;
use Petrica\Ynab\Entity\YnabVersion;
use Petrica\Ynab\IO\YnabIOInterface;
use Petrica\Ynab\Parser\YnabDiffVersionParser;
use Petrica\Ynab\Parser\YnabTransactionParser;
use Petrica\Ynab\Parser\YnabVersionParser;
use Petrica\Ynab\Utils\YnabVersionUtils;

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