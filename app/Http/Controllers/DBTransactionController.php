<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DBTransactionController extends Controller
{
    public function testTransaction() {
        // manual transaction
        // DB::beginTransaction();

        try {
            Log::info('Starting database transaction... 1');
            Log::info('Starting database transaction... 2');
            
            // throw new \Exception('Testing transaction rollback.');
            Log::info('Starting database transaction... 3');
            // throw new \Exception('Testing transaction rollback.');
            // Perform database operations here
            // Example: DB::table('users')->insert([...]);

            // Commit the transaction if everything is successful
            // DB::commit();
        } catch (\Exception $e) {
            Log::warning('error db transaction');
            // Rollback the transaction in case of an error
            // DB::rollBack();

            // Handle the exception or log it as needed
            // throw $e;
        }
    }
    // public function testTransaction() {
    //     // manual transaction
    //     DB::beginTransaction();

    //     try {
    //         Log::info('Starting database transaction... 1');
    //         Log::info('Starting database transaction... 2');
    //         sdsds
    //         // throw new \Exception('Testing transaction rollback.');
    //         Log::info('Starting database transaction... 3');
    //         // throw new \Exception('Testing transaction rollback.');
    //         // Perform database operations here
    //         // Example: DB::table('users')->insert([...]);

    //         // Commit the transaction if everything is successful
    //         DB::commit();
    //     } catch (\Exception $e) {
    //         // Rollback the transaction in case of an error
    //         DB::rollBack();
    //         Log::warning('Rollback db transaction');

    //         // Handle the exception or log it as needed
    //         // throw $e;
    //     }
    // }
}
