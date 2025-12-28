<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SubscriptionRequest;

class PaymentController extends Controller
{
    /**
     * Handle payment success redirection.
     */
    public function success(Request $request)
    {
        $user = Auth::user();


        // Find the absolute latest subscription request for the user
        $subscriptionRequest = SubscriptionRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        // Only approve if the LATEST request is strictly 'pending'
        if ($subscriptionRequest && $subscriptionRequest->status === 'pending') {


            // Update status to approved
            // The model event will automatically update the user's subscription dates
            $subscriptionRequest->update(['status' => 'approved']);
            
            return view('payment.success', [
                'plan' => $subscriptionRequest->plan,
                'user' => $user
            ]);
        }

        // If no pending request found, but user is already active, just show success
        if ($user->subscription_status === 'active') {
             return view('payment.success', [
                'plan' => $user->subscription_plan,
                'user' => $user
            ]);
        }
        
        // Fallback if no request found
        return redirect()->route('profile.show')->with('info', 'Nenhum pedido de subscrição pendente encontrado.');
    }
}
