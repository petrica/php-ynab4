<?php

namespace Petrica\Ynab\Parser;

use Petrica\Ynab\Entity\YnabTransaction;

class YnabTransactionParser implements YnabParserInterface
{
    /**
     * @var \stdClass
     */
    protected $data;

    /**
     * YnabTransactionParser constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function parse()
    {
        $transaction = new YnabTransaction();

        $transaction->setEntityId(@$this->data->entityId);
        $transaction->setCategoryId(@$this->data->categoryId);
        $transaction->setAmount(@$this->data->amount);
        $transaction->setDate(@$this->data->date);
        $transaction->setAccountId(@$this->data->accountId);

        $versionParser = new YnabVersionParser(@$this->data->entityVersion);
        $versions = $versionParser->parse();
        $version = null;
        if (count($versions)) {
            $version = array_shift($versions);
        }
        $transaction->setEntityVersion($version);
        $transaction->setMemo(@$this->data->memo);
        $transaction->setCleared(@$this->data->cleared);
        $transaction->setAccepted(@$this->data->accepted);
        $transaction->setIsTombstone(@$this->data->isTombstone ? true : false);
        $transaction->setPayeeId(@$this->data->payeeId);

        return $transaction;
    }
}