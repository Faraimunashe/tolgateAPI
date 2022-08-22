<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Paynowlog;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function deposit(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'phone' => 'required|digits:10|starts_with:07',
            'amount' => 'required|numeric'
        ]);

        $wallet = "ecocash";

        //get all data ready
        $email = $fields['email'];
        $phone = $fields['phone'];
        $amount = $fields['amount'];

        //account
        $account = Account::where('user_id', Auth::id())->first();

        /*determine type of wallet*/
        if (strpos($phone, '071') === 0) {
            $wallet = "onemoney";
        }

        $paynow = new \Paynow\Payments\Paynow(
            "11336",
            "1f4b3900-70ee-4e4c-9df9-4a44490833b6",
            route('api-deposit'),
            route('api-deposit'),
        );

        // Create Payments
        $invoice_name = "tolgate_deposit_" . time();
        $payment = $paynow->createPayment($invoice_name, $email);

        $payment->add("Tolgate Deposit", $amount);

        $response = $paynow->sendMobile($payment, $phone, $wallet);


        // Check transaction success
        if ($response->success()) {

            $timeout = 9;
            $count = 0;

            while (true) {
                sleep(3);
                // Get the status of the transaction
                // Get transaction poll URL
                $pollUrl = $response->pollUrl();
                $status = $paynow->pollTransaction($pollUrl);


                //Check if paid
                if ($status->paid()) {
                    // Yay! Transaction was paid for
                    // You can update transaction status here
                    // Then route to a payment successful
                    $info = $status->data();

                    $paynowdb = new Paynowlog();
                    $paynowdb->reference = $info['reference'];
                    $paynowdb->paynow_reference = $info['paynowreference'];
                    $paynowdb->amount = $info['amount'];
                    $paynowdb->status = $info['status'];
                    $paynowdb->poll_url = $info['pollurl'];
                    $paynowdb->hash = $info['hash'];
                    $paynowdb->save();

                    //transaction update
                    $trans = new Transaction();
                    $trans->user_id = Auth::id();
                    $trans->reference = $info['paynowreference'];
                    $trans->action = "deposit";
                    $trans->amount = $info['amount'];
                    $trans->status = "successful";
                    $trans->balance = $account->balance + $info['amount'];
                    $trans->save();

                    $account->balance = $account->balance + $info['amount'];
                    $account->save();

                    return response([
                        'message' => "Successfully deposited money",
                        'error' => false
                    ], 200);
                }


                $count++;
                if ($count > $timeout) {
                    $info = $status->data();

                    $paynowdb = new Paynowlog();
                    $paynowdb->reference = $info['reference'];
                    $paynowdb->paynow_reference = $info['paynowreference'];
                    $paynowdb->amount = $info['amount'];
                    $paynowdb->status = $info['status'];
                    $paynowdb->poll_url = $info['pollurl'];
                    $paynowdb->hash = $info['hash'];
                    $paynowdb->save();


                    //transaction update
                    $trans = new Transaction();
                    $trans->user_id = Auth::id();
                    $trans->reference = $info['paynowreference'];
                    $trans->action = "deposit";
                    $trans->amount = $info['amount'];
                    $trans->status = $info['status'];
                    $trans->balance = $account->balance;
                    $trans->save();

                    return response([
                        'message' => 'error occured wait a moment',
                        'error' => true
                    ], 400);
                } //endif
            } //endwhile
        } //endif


        //total fail
        return response([
            'message' => 'cannot perform transaction now',
            'error' => true
        ], 400);

    }

    public function transactions()
    {
        $transactions = Transaction::where('user_id', Auth::id())->all();

        return response([
            'transactions' => $transactions,
            'message' => 'success',
            'error' => false
        ], 200);
    }
}
