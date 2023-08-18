<?php

class Processes{  

    public $database;


    public $accounts;
    public $company_info;
    public $transactions;
    public $transactions_lines;

    public function __construct(){
        echo "Hello World";
    }

    // public function for mangodb connection
    public function connect(){
        // MongoDB connection settings
        $uri = "mongodb://dsw_challenge_mongodb:27017";
        $options = [];

        // Create a new MongoDB client
        $client = new MongoDB\Client($uri, $options);

        // Access the "admin" database (this step is optional)
        $adminDb = $client->admin;



        // Create a new database named "mydatabase"
        $databaseName = "mydatabase";
        $this->database = $client->$databaseName;
    }

    // public function Drop the collection's if they already exist
    public function dropCollection(){
        $this->database->dropCollection('accounts');
        $this->database->dropCollection('company_info');
        $this->database->dropCollection('transactions');
        $this->database->dropCollection('transactions_lines');
        
    }

    // public function create collection
    public function createCollection(){
        $this->accounts = $this->database->accounts;
        $this->company_info = $this->database->company_info;
        $this->transactions = $this->database->transactions;
        $this->transactions_lines = $this->database->transactions_lines;
        
    }

  
    // function to find if Journal_id exists in $invoices_csv 
    public function findJournalId($journal_id) {

        $invoices_csv = array_map('str_getcsv', file('data/invoices.csv'));

        foreach ($invoices_csv as $key => $value) {
            if ($key == 0) continue;
            if ($value[1] == $journal_id) {
                return true;
            }
        }
        return false;
    }   

 
    // Read and process accounts.csv
    public function processAccounts() {
        $accounts_csv = array_map('str_getcsv', file('data/accounts.csv'));
        foreach ($accounts_csv as $key => $value) {
            if ($key == 0) continue;
            $this->accounts->insertOne([
                'name' => $value[1],
                'code' => $value[2],
                'balance' => 0
            ]);
        }    
    }

    public function processCompanyInfo() {
        $company_csv = array_map('str_getcsv', file('data/Company.csv'));
        foreach ($company_csv as $key => $value) {
            if ($key == 0) continue;
            $this->company_info->insertOne([
                'name' => $value[0],
                'email' => $value[1],
                'address' => $value[2],
                'type' => ''
            ]);
        }   

    }

    public function processTransactions() {
        $journals_lines_csv = array_map('str_getcsv', file('data/journals_lines.csv'));
        foreach ($journals_lines_csv as $key => $value) {
            if ($key == 0) continue;
            $this->transactions_lines->insertOne([
                'transaction_ref' => $value[1],
                'account_code' => $value[2],
                'debit' => $value[3],
                'credit' => $value[4]
            ]);
        }
    
    }

    public function processTransactionsLines() {
        $journals_csv = array_map('str_getcsv', file('data/journals.csv'));
        foreach ($journals_csv as $key => $value) {
            if ($key == 0) continue;
        
            // check if journal_id exists in $invoices_csv
            if ($this->findJournalId($value[0])) {
                $value[2] = 'I';
            } else {
                $value[2] = 'J';
            }
        
            $this->transactions->insertOne([
                'ref' => $value[0],
                'description' => $value[1],
                'type' => $value[2]
            ]);
        }
    }
    
    public function AddTheCurrentBalanceToTheAccounts() {

        // Call the function to process transactions lines
        $this->processTransactionsLines();


        
        
        // 2.Add the current balance to the accounts.
        foreach ($this->accounts->find() as $account) {
         
            
        
            $balance = 0;
            
            foreach ($this->transactions_lines->find(['account_code' => $account['code']]) as $transaction_line) {
                
                // strip $ from debit and credit and convert to float
                $transaction_line['debit'] = floatval(str_replace('$', '', $transaction_line['debit']));
                $transaction_line['credit'] = floatval(str_replace('$', '', $transaction_line['credit']));


                $balance += $transaction_line['debit'] - $transaction_line['credit'];
                //echo " Balance: $balance \n";
            }
            
            $updateResult = $this->accounts->updateOne(
                ['code' => $account['code']],
                ['$set' => ['balance' => $balance]]
            );
            
            if ($updateResult->getModifiedCount() > 0) {
                //echo "Balance updated successfully.\n";
            } else {
                //echo "Balance update failed.\n";
            }
        }
    }


    // print $accounts collection
    public function printAccounts() {
        foreach ($this->accounts->find() as $account) {
            echo "Account: " . $account['name'] . "\n";
            echo "Code: " . $account['code'] . "\n";
            echo "Balance: " . $account['balance'] . "\n";
            echo "\n";
        }
    }

    // print $company_info collection
    public function printCompanyInfo() {
        foreach ($this->company_info->find() as $company) {
            echo "Company: " . $company['name'] . "\n";
            echo "Email: " . $company['email'] . "\n";
            echo "Address: " . $company['address'] . "\n";
            echo "Type: " . $company['type'] . "\n";
            echo "\n";
        }
    }

    // print $transactions collection
    public function printTransactions() {
        foreach ($this->transactions->find() as $transaction) {
            echo "Ref: " . $transaction['ref'] . "\n";
            echo "Description: " . $transaction['description'] . "\n";
            echo "Type: " . $transaction['type'] . "\n";
            echo "\n";
        }
    }

    // print $transactions_lines collection
    public function printTransactionsLines() {
        foreach ($this->transactions_lines->find() as $transaction_line) {
            echo "Transaction Ref: " . $transaction_line['transaction_ref'] . "\n";
            echo "Account Code: " . $transaction_line['account_code'] . "\n";
            echo "Debit: " . $transaction_line['debit'] . "\n";
            echo "Credit: " . $transaction_line['credit'] . "\n";
            echo "\n";
        }
    }

    // print all collection's
    public function printAllCollections() {
        $this->printAccounts();
        $this->printCompanyInfo();
        $this->printTransactions();
        $this->printTransactionsLines();
    }

 


}



?>  