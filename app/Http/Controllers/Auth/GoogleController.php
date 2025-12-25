<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->stateless()->user();
            
            $finduser = \App\Models\User::where('google_id', $googleUser->id)->first();

            if($finduser){
                \Illuminate\Support\Facades\Auth::login($finduser);
                return redirect()->intended('/');
            }else{
                // Check if user with email exists to avoid duplicates
                $user = \App\Models\User::where('email', $googleUser->email)->first();

                if ($user) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar
                    ]);
                    \Illuminate\Support\Facades\Auth::login($user);
                } else {
                    $newUser = \App\Models\User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id'=> $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'password' => bcrypt('123456dummy')
                    ]);
                    \Illuminate\Support\Facades\Auth::login($newUser);
                }

                return redirect()->intended('/');
            }

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Erro ao conectar com Google. Tente novamente.');
        }
    }
}
