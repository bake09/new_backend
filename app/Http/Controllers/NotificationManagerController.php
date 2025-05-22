<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use App\Notifications\TodoCreated;
use Illuminate\Support\Facades\Auth;

class NotificationManagerController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        Auth::user()->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth']
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request)
    {
        Auth::user()->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true]);
    }
}