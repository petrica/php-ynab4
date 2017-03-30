# PHP YNAB4 Database

Read and write transactions from and to a YNAB4 JSON database.

## Read transactions

```php
# dropbox driver
$io = new YnabDropboxIO(new Client($auth['access_token'], "MTools"));
# or
# disk driver if you want to access directly on disk the budget database
$io = new YnabDiskIO();

# path to budget folder
$pathToBudget = '/app/ynab/Test~B5C2AEE7.ynab4';
# device id as UUID Version 1, if not provided a new device will be generated
$deviceId = null;
$ynab = new YnabClient($pathToBudget, $io, $deviceId);

# fetch transactions from diff files
$ynab->pull();

# get latest transactions
$transactions = $ynab->getTransactions();

# update device knowledge based on read transactions
$ynab->commit();

# store device id for future calls
$deviceId = $ynab->getDevice()->getDeviceGUID();
```

## Write transactions

```php
# dropbox driver
$io = new YnabDropboxIO(new Client($auth['access_token'], "MTools"));
# or
# disk driver if you want to access directly on disk the budget database
$io = new YnabDiskIO();

# path to budget folder
$pathToBudget = '/app/ynab/Test~B5C2AEE7.ynab4';
# device id as UUID Version 1, if not provided a new device will be generated
$deviceId = null;
$ynab = new YnabClient($pathToBudget, $io, $deviceId);

$transaction = new YnabTransaction();
$transaction->setAccountId('UUID_ACCOUNT_TO_PUSH_TRANSACTION_TO');
$transaction->setAmount('-10.5');
$transaction->setMemo('Some memo');
$transaction->setDate(new \DateTime());

$ynab->setTransactions([
    $transaction
]);

# Create diff file for other devices to read the new transaction
$ynab->push();

# Update device knowledge based on new generated diff file
$ynab->commit();
```