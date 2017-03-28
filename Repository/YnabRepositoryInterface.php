<?php

namespace Petrica\Ynab\Repository;

interface YnabRepositoryInterface
{
    /**
     * Read entity from YNAB4 database
     *
     * @return mixed
     */
    public function read();

    /**
     * Write entity to YNAB4 database
     *
     * @param $entity mixed
     * @return mixed
     */
    public function write($entity);
}