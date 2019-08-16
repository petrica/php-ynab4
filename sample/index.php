<?php

namespace Sample;

use Petrica\Ynab\Entity\YnabTransaction;
use Petrica\Ynab\IO\YnabDiskIO;
use Petrica\Ynab\YnabClient;

require __DIR__ . '/../vendor/autoload.php';

$io = new YnabDiskIO();
$pathToBudget = __DIR__ . '/Test~D293CE43.ynab4';

// Create a new Ynab instance and specify device id under which the operations should be executed
// This is the device B
$deviceGUID = "1A66CDAA-C029-11E9-A465-414243444546";
$ynab = new YnabClient($pathToBudget, $io, $deviceGUID);

// Read current repository
$ynab->pull();

// Read new transactions if available
$transactions = $ynab->getTransactions();

// Account ID was determined by looking at the Budget.yfull file.
$accountId = "3192A8BB-0172-7830-F16F-9A65B6B6911B";

// Create new transaction in the same account as the one already existing
$transaction = new YnabTransaction();
$transaction->setAccountId($accountId);
$transaction->setAmount('-10.5');
$transaction->setMemo('Some memo');
$transaction->setDate(new \DateTime());

array_push($transactions, $transaction);

$ynab->setTransactions($transactions);

$ynab->push();
$ynab->commit();

echo "Created transaction";
print_r($transaction);