<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ .'/src/Processes.class.php';
 
use Monolog\Logger;
use Monolog\Handler\StreamHandler;





$log = new Logger('Dataswitcher Tech Challenge');
$log->pushHandler(new StreamHandler('app.log', Logger::DEBUG));
$log->info('Running Conversion');

// CALL YOUR CODE HERE

// create a new Processes object
$processes = new Processes();

// connect to mongodb
$processes->connect();

// Drop the collection's if they already exist
$processes->dropCollection();

// Create the needed collection's
$processes->createCollection();

// processAccounts
$processes->processAccounts();

// processCompanyInfo
$processes->processCompanyInfo();

// processTransactions
$processes->processTransactions();

// processTransactionsLines
$processes->processTransactionsLines();

// 2.Add the current balance to the accounts.
$processes->AddTheCurrentBalanceToTheAccounts();

// print all collection's
$processes->printAllCollections();

// prinr printAccounts
//$processes->printAccounts();

// prinr printCompanyInfo
//$processes->printCompanyInfo();

// prinr printTransactions
//$processes->printTransactions();

// prinr printTransactionsLines
//$processes->printTransactionsLines();



// END YOUR CALL HERE

$log->info('Ended Conversion');
echo "\nConversion Finished!\n";
