<?php
/**
 * !! THIS FILE SHOULD BE EXECUTED ONLY ONCE !!
 *
 * This will create a new device inside Test.ynab4/data1~D830554C/devices folder
 *
 * Note the device UUID in order to act on the database under that device
 */

namespace Sample;

use Petrica\Ynab\IO\YnabDiskIO;
use Petrica\Ynab\YnabClient;

require __DIR__ . '/../vendor/autoload.php';

$io = new YnabDiskIO();
$pathToBudget = __DIR__ . '/Test~D293CE43.ynab4';
// Create a new Ynab instance
$ynab = new YnabClient($pathToBudget, $io, null);
// Read current repository and create new device is necessary
$ynab->initialize();

// Commit new device to the database
$ynab->commit();

echo "Device GUID is: " . $ynab->getDevice()->getDeviceGUID();