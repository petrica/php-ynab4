<?php

namespace MTools\Ynab;

use CRUDlex\Entity;
use Dropbox\Client;
use Dropbox\WriteMode;
use MTools\Ynab\Diff\YnabDiff;
use MTools\Ynab\Entity\YnabTransaction;
use MTools\Ynab\Version\YnabVersion;
use Silex\Application;

require_once 'class.uuid.php';

class YnabClient
{
    /**
     * @var Application
     */
    protected $app;
    /**
     * @var Entity
     */
    protected $budget;

    /**
     * @var Client
     */
    protected $dropbox;

    /**
     * @var YnabFile
     */
    protected $budgetFile;

    /**
     * @var YnabFile
     */
    protected $deviceFile;

    /**
     * @var YnabVersion[]
     */
    protected $versions;

    /**
     * A list of all devices
     *
     * @var string[]
     */
    protected $devices;

    /**
     * @var YnabTransaction[]
     */
    protected $transactions = [];

    /**
     * YnabClient constructor.
     * @param $budget
     */
    public function __construct($budget, $app)
    {
        $this->app = $app;

        $auth = json_decode(file_get_contents(ROOT_DIR . '/config/dropbox/auth.json'), true);
        if (!isset($auth['access_token'])) {
            throw new \RuntimeException('Could not find dropbox access token in config file.');
        }

        $this->dropbox = new Client($auth['access_token'], "MTools");
        $this->budget = $budget;

        /**
         * Load device description files
         */
        $this->budgetFile = $this->getBudgetInfo();
        $devices = $this->getDevices($this->budgetFile);

        if (empty($this->budget->get('device_id'))) {
            $this->createDevice($devices);
        }
        $this->deviceFile = $this->getDeviceInfo($this->budgetFile);
        $this->versions = $this->getVersionMapping();
    }

    /**
     * @param $transaction YnabTransaction
     */
    public function addTransaction($transaction)
    {
        $this->transactions[] = $transaction;
    }

    /**
     * Read budget configuration file and return details
     * @return YnabFile
     */
    public function getBudgetInfo()
    {
        $budgetFile = $this->budget->get('path') . '/Budget.ymeta';

        return $this->readFile($budgetFile);
    }

    /**
     * @param $budgetFile
     * @return YnabFile
     */
    public function getDeviceInfo($budgetFile)
    {
        $deviceFile = $this->budget->get('path') . '/' .
            $budgetFile->getContent()->relativeDataFolderName .
            '/devices/' .
            $this->budget->get('device_short_id') .
            '.ydevice';

        return $this->readFile($deviceFile);
    }

    /**
     * Push changes to YNAB
     */
    public function push()
    {
        $path = $this->budget->get('path') . '/' .
            $this->budgetFile->getContent()->relativeDataFolderName . '/' .
            $this->deviceFile->getContent()->deviceGUID . '/' .
            $this->deviceFile->getContent()->knowledge . '_' .
            $this->getCurrentVersion()->__toString() . '.ydiff';

        $content = new YnabFile($path, $this->pack());
        $this->writeFile($content);

        $this->deviceFile->getContent()->knowledge = $this->packVersion($this->versions);
        $this->writeFile($this->deviceFile);
    }

    /**
     * Read file from Dropbox
     *
     * @param $path
     * @return YnabFile
     */
    public function readFile($path)
    {
        $stream = fopen('php://memory', 'rw+');
        $meta = $this->dropbox->getFile($path, $stream);
        if (!$meta) {
            throw new \RuntimeException(sprintf('Could not find file %s', $path));
        }
        rewind($stream);
        $file = new YnabFile($meta['path'], stream_get_contents($stream));

        return $file;
    }

    /**
     * @param $file YnabFile
     */
    public function writeFile($file)
    {
        $meta = $this->dropbox->uploadFileFromString($file->getPath(), WriteMode::force(), $file->__toString());
        if (null === $meta) {
            throw new \RuntimeException(sprintf('Could not write file to dropbox %s', $file->getPath()));
        }

        return $meta;
    }

    /**
     * Based on current device knowledge, determine new entity version
     */
    public function getCurrentVersion()
    {
        $short = $this->deviceFile->getContent()->shortDeviceId;
        if (!isset($this->versions[$short])) {
            throw new \RuntimeException(sprintf('Could not find version for device %s', $short));
        }

        return $this->versions[$short];
    }

    /**
     * Based on device knowledge, get version mapping of each device
     * [
     *  'A' => YnabVersion()
     * ]
     */
    protected function getVersionMapping()
    {
        $knowledge = $this->deviceFile->getContent()->knowledge;
        $devices = explode(',', $knowledge);
        $versions = [];
        foreach ($devices as $ver) {
            list($device, $version) = explode('-', $ver);
            $version = new YnabVersion($device, $version);
            $versions[$device] = $version;
        }
        ksort($versions);

        return $versions;
    }

    /**
     * Pack version based on versions string
     *
     * @param $versions YnabVersion[]
     */
    protected function packVersion($versions)
    {
        $pack = [];
        ksort($versions);
        foreach ($versions as $version) {
            $pack[] = $version->__toString();
        }

        return implode(',', $pack);
    }

    /**
     * Pack json for sync
     */
    protected function pack()
    {
        $json = [
            "startVersion" => $this->deviceFile->getContent()->knowledge,
            "endVersion" => $this->packVersion($this->versions),
            "formatVersion" => null,
            "publishTime" => date('D M j H:i:s \G\M\TO Y'),
            "deviceGUID" => $this->deviceFile->getContent()->deviceGUID,
            "shortDeviceId" => $this->deviceFile->getContent()->shortDeviceId,
            "dataVersion" => "4.2",
            "budgetDataGUID" => null,
            "items" => []
        ];

        $json['items'] = array_merge($this->transactions);

        return $json;
    }

    /**
     * Scan available devices
     *
     */

    /**
     * @param $budgetFile YnabFile
     * @return array|\string[]
     */
    protected function getDevices($budgetFile)
    {
        $path = $this->budget->get('path') . '/' .
            $budgetFile->getContent()->relativeDataFolderName . '/' .
            'devices';

        $this->devices = [];

        $meta = $this->dropbox->getMetadataWithChildren($path);
        if (null !== $meta && isset($meta['contents'])) {
            foreach ($meta['contents'] as $file) {
                $info = pathinfo($file['path']);
                if (count($info['filename']) == 1) {
                    $this->devices[] = $info['filename'];
                }
                else {
                    throw new \RuntimeException(sprintf('Could not get device name, filename length does not equal to 1 %s', $info['filename']));
                }
            }
        }
        else {
            throw new \RuntimeException('Could not find any devices.');
        }

        return $this->devices;
    }

    /**
     * Create a new device by knowing the previous known devies
     *
     * @param $devices
     */
    protected function createDevice($devices)
    {
        $all = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $device = null;
        for ($i = 0; $i < strlen($all); $i++) {
            if (!in_array($all[$i], $devices)) {
                $device = $all[$i];
                break;
            }
        }

        if (null === $device) {
            throw new \RuntimeException('Could not find a available device short id');
        }

        $path = $this->budget->get('path') . '/' .
            $this->budgetFile->getContent()->relativeDataFolderName .
            '/devices/' .
            $device .
            '.ydevice';

        $content = [
            "YNABVersion" => "MTools",
            "deviceGUID" => strtoupper(\UUID::generate(\UUID::UUID_TIME, \UUID::FMT_STRING, "ABCDEF")),
            "deviceType" => "Web",
            "formatVersion" => "1.2",
            "friendlyName" => "MTools",
            "hasFullKnowledge" => false,
            "highestDataVersionImported" => "4.2",
            "knowledge" => $device . "-0",
            "knowledgeInFullBudgetFile" => null,
            "lastDataVersionFullyKnown" => "4.2",
            "shortDeviceId" => $device
        ];

        $deviceFile = new YnabFile($path, $content);
        if ($this->writeFile($deviceFile)) {
            $this->budget->set('device_short_id', $device);
        }

        if (!$this->app['crud']->getData('ynab_budget')->update($this->budget)) {
            throw new \RuntimeException(sprintf('Could not update budget entity %d.', $this->budget->get('id')));
        }
    }

    protected function getDeviceFiles($device) {

        $deviceFile = $this->budget->get('path') . '/' .
            $this->budgetFile->getContent()->relativeDataFolderName .
            '/devices/' .
            $device .
            '.ydevice';

        $info = $this->readFile($deviceFile);

        $path = $this->budget->get('path') . '/' .
            $this->budgetFile->getContent()->relativeDataFolderName . '/' .
            $info->getContent()->deviceGUID;

        $meta = $this->dropbox->getMetadataWithChildren($path);

        $diffs = [];
        if (null !== $meta && isset($meta['contents'])) {
            foreach ($meta['contents'] as $file) {
                $info = pathinfo($file['path']);

                if ($info['extension'] == 'ydiff') {
                    $name = $info['filename'];

                    list($from, $to) = explode('_', $name);

                    list($dev, $ver) = explode('-', $to);
                    $to = new YnabVersion($dev, $ver);

                    /**
                     * Get versions from
                     */
                    $devices = explode(',', $from);
                    /** @var YnabVersion[] $from */
                    $from = [];
                    foreach ($devices as $ver) {
                        list($dev, $version) = explode('-', $ver);
                        $version = new YnabVersion($dev, $version);
                        $from[$dev] = $version;
                    }
                    ksort($from);

                    // Determine if diff is valid
                    $isDiff = false;
                    if (isset($this->versions[$device])) {
                        /**
                         * Is a new version for this device version?
                         */
                        if ($to->getIncrement() > $this->versions[$device]->getIncrement()) {
                            $isDiff = true;
                        }
                    }
                    else {
                        // This version has never been ingested
                        $isDiff = true;
                    }

                    if ($isDiff) {
                        $diffs[] = new YnabDiff($file['path'], $from, $to);
                    }
                }
            }
        }

        return $diffs;
    }

    /**
     * Get only transactions from items
     *
     * @param $items
     */
    protected function parseTransactions($items)
    {
        $transactions = [];
        foreach ($items as $item) {
            if ($item->entityType == 'transaction') {
                $trans = new YnabTransaction();
                $trans->setEntityId(@$item->entityId);
                $trans->setAccountId(@$item->accountId);
                $trans->setCategoryId(@$item->categoryId);
                $trans->setDate(@$item->date);
                $trans->setAmount(@$item->amount);
                $trans->setEntityVersion(@$item->entityVersion);
                $trans->setAccepted(@$item->accepted);
                $trans->setPayeeId(@$item->payeeId);
                $trans->setMemo(@$item->memo);
                $trans->setIsTombstone(@$item->isTombstone);

                $transactions[] = $trans;
            }
        }

        return $transactions;
    }

    /**
     * Pull changes from YNAB
     */
    public function pull()
    {
        /** @var YnabDiff[] $files */
        $files = [];
        foreach ($this->devices as $device) {
            $files = array_merge($files, $this->getDeviceFiles($device));
        }

        $this->transactions = [];
        if ($files) {
            $sort = [];
            foreach ($files as $file) {
                $sort[] = $file->getDistance();
            }

            /**
             * Sort by the lowest distance
             */
            array_multisort($files, $sort, SORT_NUMERIC, SORT_ASC);

            foreach ($files as $file) {
                $content = $this->readFile($file->getPath());

                $items = $content->getContent()->items;

                $this->transactions = array_merge($this->transactions, $this->parseTransactions($items));

                $this->versions[$file->getTo()->getShortId()] = $file->getTo();
            }
        }
    }

    /**
     * Commit pull command that the update is successful
     */
    public function commit()
    {
        $this->deviceFile->getContent()->knowledge = $this->packVersion($this->versions);
        $this->writeFile($this->deviceFile);
    }

    /**
     * @return YnabTransaction[]
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
}