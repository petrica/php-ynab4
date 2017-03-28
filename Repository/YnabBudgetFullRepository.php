<?php

namespace MTools\Ynab\Repository;

use MTools\Ynab\Entity\YnabDevice;
use MTools\Ynab\Entity\YnabEntities;
use MTools\Ynab\IO\YnabIOInterface;
use MTools\Ynab\Parser\YnabTransactionParser;

/**
 * Class YnabBudgetFullRepository
 *
 * @package MTools\Ynab\Repository
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