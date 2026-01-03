<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the user profile form.
     */
    public function show()
    {
        $user = Auth::user();
        $categories = Category::all();
        return view('profile.show', compact('user', 'categories'));
    }

    /**
     * Update the user profile (CV and Categories).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'sex' => 'nullable|string|in:Masculino,Feminino',
            'birth_date' => 'nullable|date',
            'cv' => 'nullable|mimes:pdf|max:2048', // Max 2MB PDF
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
        ]);

        // Update Basic Info
        $user->update([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'sex' => $request->sex,
            'birth_date' => $request->birth_date,
        ]);

        // Handle CV Upload
        if ($request->hasFile('cv')) {
            // Delete old CV if exists
            if ($user->cv_path) {
                Storage::disk('public')->delete($user->cv_path);
            }
            // Store new CV
            // Store new CV
            $userName = \Illuminate\Support\Str::slug($user->name);
            $hashName = $request->file('cv')->hashName(); // Generates random string + extension
            $filename = "CV - {$userName} - {$hashName}";
            
            $path = $request->file('cv')->storeAs('cvs', $filename, 'public');
            $user->update(['cv_path' => $path]);
        }

        // Handle Categories Sync
        if ($request->has('categories')) {
            $user->categories()->sync($request->categories);
        } else {
            // If no categories sent (unchecked all), detach all
            $user->categories()->detach();
        }

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Show jobs matching user's categories.
     */
    public function potentialJobs()
    {
        $user = Auth::user();
        $userCategoryIds = $user->categories->pluck('id');

        // Fetch jobs that belong to any of the user's categories
        $jobs = Job::whereHas('categories', function ($query) use ($userCategoryIds) {
            $query->whereIn('categories.id', $userCategoryIds);
        })
        ->with('categories')
        ->latest()
        ->paginate(12);

        return view('jobs.potential', compact('jobs'));
    }

    /**
     * Show subscription plans.
     */
    public function plans()
    {
        return view('plans');
    }

    /**
     * Show confirmation page with requirement checks.
     */
    public function confirm(Request $request)
    {
        $plan = $request->query('plan');
        $validPlans = ['weekly', 'monthly', 'quarterly', 'yearly'];

        if (!in_array($plan, $validPlans)) {
            return redirect()->route('plans.index');
        }

        $user = Auth::user();
        $hasCv = !empty($user->cv_path);
        $hasCategories = $user->categories()->count() > 0;
        
        $isSubscriptionActive = $user->subscription_status === 'active' 
            && $user->subscription_end 
            && \Carbon\Carbon::parse($user->subscription_end)->isFuture();

        $canSubscribe = $hasCv && $hasCategories && !$isSubscriptionActive;

        return view('plans.confirm', compact('plan', 'hasCv', 'hasCategories', 'canSubscribe', 'isSubscriptionActive'));
    }

    /**
     * Handle subscription interest.
     */
    /**
     * Handle subscription interest with payment intent creation.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:weekly,monthly,quarterly,yearly',
        ]);

        $user = Auth::user();

        // Server-side validation just in case
        if (empty($user->cv_path) || $user->categories()->count() == 0) {
            return redirect()->route('plans.index')->with('error', 'Requisitos não cumpridos.');
        }

        // Check if user already has an active subscription with valid date
        if ($user->subscription_status === 'active' && $user->subscription_end && \Carbon\Carbon::parse($user->subscription_end)->isFuture()) {
             return redirect()->route('profile.show')->with('info', 'Já possui uma subscrição ativa.');
        }
        
        $planIds = [
            'weekly' => '856ed35c-7b33-4e98-9352-954d22bc56a2',
            'monthly' => '2dc13ec4-5a1d-4ddf-a5f0-aa5324e29c39',
            'quarterly' => '40074bfc-54a0-4429-97ba-30955cc5cce3',
            'yearly' => '22850349-da68-4fe2-a453-3d6884c5df16',
        ];

        $offerId = $planIds[$request->plan] ?? null;

        if (!$offerId) {
             return redirect()->route('plans.index')->with('error', 'Plano inválido.');
        }

        // Create initial subscription request
        $subscriptionRequest = \App\Models\SubscriptionRequest::create([
            'user_id' => $user->id,
            'plan' => $request->plan,
            'status' => 'pending',
        ]);

        // Call Kuenha API
        try {
            $payload = [
                "offerId" => $offerId,
                "bumps" => [],
                "payMethod" => "GPO_MCX",
                "buyerId" => env('KUENHA_BUYER_ID'),
                "trackingParams" => [
                    "src" => null,
                    "sck" => null,
                    "utm_source" => null,
                    "utm_campaign" => null,
                    "utm_medium" => null,
                    "utm_content" => null,
                    "utm_term" => null
                ]
            ];

            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(60)
                ->withBody(json_encode($payload), 'application/json')
                ->post(env('KUENHA_API_URL'));

            if ($response->successful()) {
                $data = $response->json();
                $saleId = $data['id']; // This is the token/sale_id
                
                // Update subscription request with sale_id
                $subscriptionRequest->update(['sale_id' => $saleId]);
                $user->update(['subscription_status' => 'pending']);

                return view('plans.payment', [
                    'token' => $saleId,
                    'subscriptionRequestId' => $subscriptionRequest->id
                ]);

            } else {
                 \Illuminate\Support\Facades\Log::error('Kuenha Payment API Failed', ['status' => $response->status(), 'body' => $response->body()]);
                 // Dump body to see the error
                 return redirect()->route('plans.index')->with('error', 'Erro API (' . $response->status() . '): ' . $response->body());
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Kuenha Payment Error: ' . $e->getMessage());
            return redirect()->route('plans.index')->with('error', 'Erro Sistema: ' . $e->getMessage());
        }
    }

    /**
     * Check subscription status for polling.
     */
    public function checkStatus($id)
    {
        $subscriptionRequest = \App\Models\SubscriptionRequest::find($id);

        if (!$subscriptionRequest) {
            return response()->json(['status' => 'error'], 404);
        }

        return response()->json(['status' => $subscriptionRequest->status]);
    }
}
