<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {

        $user = $request->user();
        $subscription = $user->subscription ?: new Subscription();
        $subscription->user_id = $user->id;
        $subscription->plan = $request->plan;
        $subscription->save();

        return redirect()->route('dashboard');
    }
}
