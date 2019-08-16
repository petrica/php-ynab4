<?php

namespace Petrica\Ynab;

use Petrica\Ynab\Entity\YnabBudget;
use Petrica\Ynab\Entity\YnabDiff;
use Petrica\Ynab\Entity\YnabItem;
use Petrica\Ynab\Repository\YnabBudgetRepository;
use Petrica\Ynab\Entity\YnabDevice;
use Petrica\Ynab\Repository\YnabDeviceRepository;
use Petrica\Ynab\Entity\YnabTransaction;
use Petrica\Ynab\Entity\YnabVersion;
use Petrica\Ynab\IO\YnabIOInterface;
use Petrica\Ynab\Repository\YnabBudgetFullRepository;
use Petrica\Ynab\Repository\YnabDiffRepository;
use Petrica\Ynab\Utils\YnabVersionUtils;

require_once 'class.uuid.php';

/**
 * Class YnabClient
 *
 * Push/Pull changes from a YNAB4 database
 *
 * @package Petrica\Ynab
 */
class YnabClient
{
    /**
     * @var string
     */
    protected $budgetFilepath;

    /**
     * @var string
     */
    protected $deviceGUID;

    /**
     * @var string
     */
    protected $deviceShortId;

    /**
     * @var YnabIOInterface
     */
    protected $io;

    /**
     * @var YnabBudget
     */
    protected $budget;

    /**
     * @var YnabDevice[]
     */
    protected $devices;

    /**
     * @var bool If device was found and full sync is required
     */
    protected $isFullSyncRequired = false;

    /**
     * @var YnabTransaction[]
     */
    protected $transactions;

    /**
     * If the client is fully initialized
     *
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * YnabClient constructor.
     *
     * @param $budgetFilepath string File path to YANB4 budget file .ynab4
     * @param null $deviceGUID string device GUID to push/pull changes to/from
     */
    public function __construct($budgetFilepath, $io, $deviceGUID = null)
    {
        $this->io = $io;
        $this->budgetFilepath = $budgetFilepath;
        $this->deviceGUID = $deviceGUID;

        $this->transactions = [];
    }

    /**
     * Read budget definition file and create new device if necessary
     */
    public function initialize()
    {
        if ($this->isInitialized) {
            return;
        }
        /**
         * Load budget file
         */
        $repo = new YnabBudgetRepository($this->budgetFilepath, $this->io);
        $this->budget = $repo->read();

        /**
         * Load devices definition files
         */
        $repo = new YnabDeviceRepository($this->budget, $this->io);
        $this->devices = $repo->read();

        /**
         * Identify device short ID
         */
        foreach ($this->devices as $device)
        {
            if ($device->getDeviceGUID() == $this->deviceGUID) {
                $this->deviceShortId = $device->getShortDeviceId();
                break;
            }
        }

        /**
         * Cold not identify device short id, create a new device file
         */
        if (null === $this->deviceShortId) {
            $this->createDevice();
        }

        $this->isInitialized = true;
    }

    /**
     * Pull changes from other devices
     */
    public function pull()
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }

        if ($this->isFullSyncRequired) {
            $this->sync();
        }

        $repo = new YnabDiffRepository($this->devices, $this->getDevice()->getKnowledge(), $this->io);
        $budgetFull = $repo->read();

        $this->getDevice()->setKnowledge($budgetFull->getKnowledge());

        $this->transactions = array_merge($this->transactions, $budgetFull->getTransactions());

        return true;
    }

    /**
     * Push changes for current device
     */
    public function push()
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }

        if ($this->isFullSyncRequired) {
            throw new \RuntimeException('Full sync is required before pushing changes.');
        }

        $diff = new YnabDiff();
        $diff->setStartVersion($this->getDevice()->getKnowledge());
        $diff->setPublishTime(date('D M j H:i:s \G\M\TO Y'));
        $diff->setDeviceGUID($this->getDevice()->getDeviceGUID());
        $diff->setShortDeviceId($this->getDevice()->getShortDeviceId());

        $utils = new YnabVersionUtils();

        $knowledge = $this->getDevice()->getKnowledge();
        if (!isset($knowledge[$this->getDevice()->getShortDeviceId()])) {
            throw new \RuntimeException(sprintf('Could not find knowledge version for device %d', $this->getDevice()->getShortDeviceId()));
        }

        $items = [];
        foreach ($this->transactions as $transaction) {
            if (null == $transaction->getEntityVersion()) {
                /**
                 * Create new version
                 */
                $increment = $knowledge[$this->getDevice()->getShortDeviceId()]->getIncrement() + 1;
                $version = new YnabVersion($this->getDevice()->getShortDeviceId(), $increment);
                $transaction->setEntityVersion($version);
                $knowledge[$this->getDevice()->getShortDeviceId()] = $version;
            }
            else {
                /**
                 * Skip this transaction
                 */
                if ($utils->isNewKnowledge($transaction->getEntityVersion(), $this->getDevice()->getKnowledge())) {
                    $knowledge = $utils->mergeKnowledge($knowledge, [ $transaction->getEntityVersion() ]);
                }
                else {
                    continue;
                }
            }

            $items[] = $transaction;
        }
        $diff->setItems($items);
        $diff->setEndVersion($knowledge);

        $this->getDevice()->setKnowledge($knowledge);

        // Mark other devices as not having full knowledge
        if (count($items)) {
            foreach ($this->devices as $device) {
                if ($device->getDeviceGUID() != $this->getDevice()->getDeviceGUID()) {
                    $device->setHasFullKnowledge(false);
                }
            }
        }

        $repo = new YnabDiffRepository($this->devices, $this->getDevice()->getKnowledge(), $this->io);
        return $repo->write($diff);
    }

    /**
     * Commit device related information to YNAB4 database
     */
    public function commit()
    {
        $repo = new YnabDeviceRepository($this->budget, $this->io);
        $success = true;
        foreach ($this->devices as $device) {
            if (!$repo->write($device)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Read full knowledge from a device
     */
    public function sync()
    {
        // find device that has the full database
        $device = null;
        foreach ($this->devices  as $dev) {
            if ($dev->getHasFullKnowledge()) {
                $device = $dev;
                break;
            }
        }

        if (null === $device) {
            throw new \RuntimeException('Could not find any device that has the full knowledge');
        }

        /**
         * Read content of the full budget file
         */
        $repo = new YnabBudgetFullRepository($device, $this->io);
        $budgetFull = $repo->read();

        $this->transactions = array_merge($this->transactions, $budgetFull->getTransactions());

        /**
         * Merge full budget history knowledge with device knowledge
         */
        $versionUtils = new YnabVersionUtils();
        $this->getDevice()->setKnowledge(
            $versionUtils->mergeKnowledge($device->getKnowledgeInFullBudgetFile(), $this->getDevice()->getKnowledge())
        );

        return true;
    }

    /**
     * @return YnabTransaction[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param YnabTransaction[] $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * Create new device definition
     */
    protected function createDevice()
    {
        $all = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->deviceShortId = null;
        $devices = array_keys($this->devices);
        for ($i = 0; $i < strlen($all); $i++) {
            if (!in_array($all[$i], $devices)) {
                $this->deviceShortId = $all[$i];
                break;
            }
        }

        if (null === $this->deviceShortId) {
            throw new \RuntimeException('Could not find a available device short id');
        }

        $version = new YnabVersion($this->deviceShortId, 0);
        $this->deviceGUID = strtoupper(\UUID::generate(\UUID::UUID_TIME, \UUID::FMT_STRING, "ABCDEF"));

        $device = new YnabDevice();
        $device->setYNABVersion('MTools');
        $device->setDeviceGUID($this->deviceGUID);
        $device->setDeviceType('Web');
        $device->setFormatVersion('1.2');
        $device->setFriendlyName('MTools');
        $device->setHasFullKnowledge(false);
        $device->setHighestDataVersionImported('4.2');
        $device->setKnowledge([
            $version->getShortId() => $version
        ]);
        $device->setKnowledgeInFullBudgetFile(null);
        $device->setLastDataVersionFullyKnown('4.2');
        $device->setShortDeviceId($this->deviceShortId);
        $device->setDataFullPath($this->budgetFilepath . '/' . $this->deviceGUID);

        $this->devices[$this->deviceShortId] = $device;
        $this->isFullSyncRequired = true;
    }

    /**
     * Get current device
     * @return YnabDevice
     */
    public function getDevice()
    {
        if (!isset($this->devices[$this->deviceShortId])) {
            throw new \RuntimeException(sprintf('Could not find device %d in the available device list', $this->deviceShortId));
        }

        return $this->devices[$this->deviceShortId];
    }
}