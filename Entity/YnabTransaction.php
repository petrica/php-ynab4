<?php

namespace Petrica\Ynab\Entity;

use Petrica\Ynab\Utils\YnabVersionUtils;

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

    protected $dirty = [];

    public function __construct()
    {
        $this->setCleared(static::CLEARED_TRUE);
        $this->addDirty('entityType');

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
        $this->addDirty('amount');
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
        $this->addDirty('entityVersion');
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
        $this->addDirty('accepted');
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
        $this->addDirty('cleared');
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
        $this->addDirty('date');
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
        $this->addDirty('categoryId');
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
        $this->addDirty('payeeId');
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
        $this->addDirty('entityId');
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
        $this->addDirty('accountId');
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
        $this->addDirty('memo');
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
        $this->addDirty('isTombstone');
    }

    /**
     * Add to dirty fields array
     *
     * @param $field
     */
    protected function addDirty($field)
    {
        if (!in_array($field, $this->dirty)) {
            $this->dirty[] = $field;
        }
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        $dirty = array_flip($this->dirty);
        $utils = new YnabVersionUtils();
        $data = get_object_vars($this);
        $data['entityVersion'] = $utils->packKnowledge([
            $this->getEntityVersion()
        ]);
        if (isset($dirty['date'])) {
            $date = $this->getDate();
            if (!$date instanceof \DateTime) {
                $date = new \DateTime($this->getDate());
            }
            $data['date'] = $date->format('Y-m-d');
        }
        return array_intersect_key($data, $dirty);
    }
}