<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class FcmApiController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_name' => 'nullable|string'
        ]);

        $userId = $request->user()->id_user;

        DB::table('fcm_tokens')->updateOrInsert(
            ['fcm_token' => $request->fcm_token],
            [
                'user_id' => $userId,
                'device_name' => $request->device_name,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM token registered successfully'
        ]);
    }
}
