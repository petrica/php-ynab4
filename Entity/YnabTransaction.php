<?php

namespace Petrica\Ynab\Entity;

require_once 'class.uuid.php';

/**
 * Class YnabTransaction
 * @package Petrica\Ynab\Entity
 */
class YnabTransaction implements \JsonSerializable
{
    const CLEARED_TRUE = 'Cleared';
    const CLEARED_FALSE = 'Uncleared';

    protected $amount;
    protected $entityVersion;
    protected $accepted = true;
    protected $cleared;
    protected $date;
    protected $categoryId;
    protected $payeeId;
    protected $entityId;
    protected $accountId;
    protected $entityType = 'transaction';
    protected $memo;
    protected $isTombstone = false;

    public function __construct()
    {
        $this->setCleared(static::CLEARED_TRUE);

        $this->setEntityId(strtoupper(\UUID::generate(\UUID::UUID_TIME, \UUID::FMT_STRING, "ABCDEF")));
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getEntityVersion()
    {
        return $this->entityVersion;
    }

    /**
     * @param mixed $entityVersion
     */
    public function setEntityVersion($entityVersion)
    {
        $this->entityVersion = $entityVersion;
    }

    /**
     * @return bool
     */
    public function isAccepted()
    {
        return $this->accepted;
    }

    /**
     * @param bool $accepted
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;
    }

    /**
     * @return mixed
     */
    public function getCleared()
    {
        return $this->cleared;
    }

    /**
     * @param mixed $cleared
     */
    public function setCleared($cleared)
    {
        $this->cleared = $cleared;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return mixed
     */
    public function getPayeeId()
    {
        return $this->payeeId;
    }

    /**
     * @param mixed $payeeId
     */
    public function setPayeeId($payeeId)
    {
        $this->payeeId = $payeeId;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param mixed $accountId
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return mixed
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @param mixed $memo
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;
    }

    /**
     * @return bool
     */
    public function isIsTombstone()
    {
        return $this->isTombstone;
    }

    /**
     * @param bool $isTombstone
     */
    public function setIsTombstone($isTombstone)
    {
        if ($isTombstone) {
            $this->isTombstone = true;
        }
        else {
            $this->isTombstone = false;
        }
    }

    function jsonSerialize()
    {
        return get_object_vars($this);
    }
}