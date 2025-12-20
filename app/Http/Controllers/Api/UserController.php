<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('categories')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'cv' => $user->cv_url, // Uses the accessor we created earlier
                    'categories' => $user->categories,
                ];
            });

        return response()->json($users);
    }
}
