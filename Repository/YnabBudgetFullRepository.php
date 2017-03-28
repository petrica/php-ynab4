<?php

namespace Petrica\Ynab\Repository;

use Petrica\Ynab\Entity\YnabDevice;
use Petrica\Ynab\Entity\YnabEntities;
use Petrica\Ynab\IO\YnabIOInterface;
use Petrica\Ynab\Parser\YnabTransactionParser;

/**
 * Class YnabBudgetFullRepository
 *
 * @package Petrica\Ynab\Repository
 */
class YnabBudgetFullRepository implements YnabRepositoryInterface
{
    /**
     * @var YnabDevice
     */
    protected $device;

    /**
     * @var YnabIOInterface
     */
    protected $io;

    /**
     * @var string Path to budget full
     */
    protected $path;

    /**
     * YnabBudgetFullRepository constructor.
     * @param $device
     * @param $io
     */
    public function __construct($device, $io)
    {
        $this->device = $device;
        $this->io = $io;
        $this->path = $this->device->getDataFullPath() . '/Budget.yfull';
    }

    /**
     * @return YnabEntities
     */
    public function read()
    {
        $file = $this->io->read($this->path);

        $data = $file->getContent();

        $budgetFull = new YnabEntities();

        /**
         * Parser transactions from full file
         */
        $transactions = [];
        if (isset($data->transactions)) {
            foreach ($data->transactions as $raw) {
                $parser = new YnabTransactionParser($raw);

                $transactions[] = $parser->parse();
            }
        }
        $budgetFull->setTransactions($transactions);

        return $budgetFull;
    }

    /**
     * Read only repository
     *
     * @param mixed $entity
     * @return bool
     */
    public function write($entity)
    {
        return false;
    }

}