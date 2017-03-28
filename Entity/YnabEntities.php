<?php

namespace MTools\Ynab\Entity;

class YnabEntities
{
    /**
     * @var YnabTransaction[]
     */
    private $transactions = [];

    /**
     * @var YnabVersion[]
     */
    private $knowledge;

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
     * @return YnabVersion[]
     */
    public function getKnowledge()
    {
        return $this->knowledge;
    }

    /**
     * @param YnabVersion[] $knowledge
     */
    public function setKnowledge($knowledge)
    {
        $this->knowledge = $knowledge;
    }
}