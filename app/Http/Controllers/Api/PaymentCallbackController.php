<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Get all pending subscription requests with a sale_id
        $pendingRequests = SubscriptionRequest::where('status', 'pending')
            ->whereNotNull('sale_id')
            ->get();

        $processed = 0;
        $updated = 0;
        $deleted = 0;

        foreach ($pendingRequests as $subRequest) {
            $saleId = $subRequest->sale_id;

            try {
                // 2. Call EMIS API for each request
                // Using withoutVerifying() to avoid SSL issues in dev environment as requested before
                $response = Http::withoutVerifying()->get("https://pagamentonline.emis.co.ao/online-payment-gateway/webframe/v1/frameToken/{$saleId}");

                $data = $response->json();
                
                // Log for debugging
                Log::info("Checking Sale ID: {$saleId}", ['response' => $data]);

                // 3. Logic based on response
                if (isset($data['code']) && $data['code'] == '201' && isset($data['message']) && $data['message'] == 'internal error') {
                    // Payment invalid/not found/expired - Delete request
                    $subRequest->delete();
                    $deleted++;
                    Log::info("Deleted subscription request ID {$subRequest->id} due to internal error (expiry/invalid).");
                    continue;
                }

                if (isset($data['status'])) {
                    if ($data['status'] === 'USED' 
                        && isset($data['transationDetails']['status']) 
                        && $data['transationDetails']['status'] === 'ACCEPTED') {
                        
                        // Payment Successful
                        $subRequest->update(['status' => 'approved']);
                        $updated++;
                        Log::info("Approved subscription request ID {$subRequest->id}.");
                    } 
                    // If 'ACTIVE', do nothing (still pending)
                }

            } catch (\Exception $e) {
                Log::error("Error checking status for sale_id {$saleId}: " . $e->getMessage());
            }

            $processed++;
        }

        return response()->json([
            'message' => 'Processed pending subscriptions',
            'processed' => $processed,
            'updated' => $updated,
            'deleted' => $deleted
        ]);
    }
}
