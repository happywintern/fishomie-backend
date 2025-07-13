<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Subscription;
use Log;

class PaymentController extends Controller
{
    // Handle the webhook notification from Midtrans
    public function handleNotification(Request $request)
    {
        Log::info('Webhook received', $request->all());

        $notification = $request->all();

        if (isset($notification['order_id'])) {
            $subscription = Subscription::where('transaction_id', $notification['order_id'])->first();

            if ($subscription) {
                if ($notification['transaction_status'] == 'settlement') {
                    $subscription->update(['status' => 'paid']);
                    $subscription->user->update(['status' => 'member']); // Update user status to 'member'
                    Log::info("Subscription {$subscription->id} updated to paid.");
                } elseif ($notification['transaction_status'] == 'expire') {
                    $subscription->update(['status' => 'expired']);
                    Log::info("Subscription {$subscription->id} updated to expired.");
                }
            } else {
                Log::error("Subscription not found for transaction ID: " . $notification['order_id']);
            }
        } else {
            Log::warning("Received webhook without 'order_id'. Data: " . json_encode($notification));
        }

        return response()->json(['status' => 'success'], 200);
    }

    // Charge the user for the transaction and generate the Snap token
    public function chargeTransaction(Request $request)
    {
        // Configure Midtrans server key and environment
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', true);

        // Get the authenticated user and transaction details
        $user = auth()->user();
        $amount = 10000; // Default amount
        $transactionId = uniqid(); // Unique transaction ID

        // Prepare transaction details
        $transactionDetails = [
            'transaction_details' => [
                'order_id' => $transactionId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->username ?? 'Subscriber', // Use actual data if available
                'email' => $user->email ?? 'example@example.com',
            ],
            'enabled_payments' => ['gopay'], // You can adjust the payment method
            'expiry' => [
                'start_time' => date("Y-m-d H:i:s O"),
                'unit' => 'minutes',
                'duration' => 10,
            ],
        ];

        // Log transaction details for debugging
        Log::info('Preparing charge transaction', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'transaction_details' => $transactionDetails,
            'user_id' => $user->id ?? 'No user ID',
        ]);

        try {
            // Attempt to get Snap Token from Midtrans
            $snapToken = Snap::getSnapToken($transactionDetails);

            // Log the Snap token received
            Log::info('Received Snap Token', [
                'snap_token' => $snapToken,
                'transaction_id' => $transactionId,
            ]);

            // Save the transaction in the database
            Subscription::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(10),
                'transaction_id' => $transactionId,
            ]);

            // Log the transaction save success
            Log::info('Transaction saved in database', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
            ]);

            Log::info("Returning snap_token", [
                'snap_token' => $snapToken
            ]);
            

            // Return the Snap token in the response
            return response()->json([
                'status' => 'success',
                'snap_token' => $snapToken,
            ], 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            // Log the exception if Snap token generation fails
            Log::error('Error in chargeTransaction', [
                'message' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            // Return error response if something goes wrong
            return response()->json(['status' => 'error', 'message' => 'Failed to generate Snap token'], 500);
        }
    }
}
