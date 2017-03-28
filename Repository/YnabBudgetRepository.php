<?php

namespace MTools\Ynab\Repository;
use MTools\Ynab\Entity\YnabBudget;
use MTools\Ynab\IO\YnabIOInterface;

/**
 * Class YnabBudgetRepository
 *
 * @package MTools\Ynab\Entity
 */
class YnabBudgetRepository implements YnabRepositoryInterface
{
    /**
     * @var string
     */
    protected $budgetPath;

    /**
     * @var YnabIOInterface
     */
    protected $io;

    /**
     * BudgetParser constructor.
     *
     * @param $budgetPath
     * @param $io
     */
    public function __construct($budgetPath, $io)
    {
        $this->budgetPath = $budgetPath;
        $this->io = $io;
    }

    /**
     * Parse budget data and return budget instance
     *
     * @return YnabBudget
     */
    public function read()
    {
        $path = $this->budgetPath . '/Budget.ymeta';
        $dataFile = $this->io->read($path);
        $budget = new YnabBudget();

        $budget->setFormatVersion($dataFile->getContent()->formatVersion);
        $budget->setRelativeDataFolderName($dataFile->getContent()->relativeDataFolderName);
        $budget->setTED($dataFile->getContent()->TED);
        $budget->setFullDataPath($this->budgetPath . '/' . $budget->getRelativeDataFolderName());

        return $budget;
    }

    /**
     * Only read access to budget file
     *
     * @param mixed $entity
     * @return bool
     */
    public function write($entity)
    {
        return false;
    }


}