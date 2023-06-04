<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class TokenController extends Controller
{

    public function issue_token(Request $request)
    {
        try {
            $plain_token = Str::random(40);
            $expired_at = Carbon::now()->addWeek();

            $token = Token::create([
                'token' => $plain_token,
                'expired_at' => $expired_at
            ]);

            return response()->json([
                'token' => $plain_token,
                'expired_at' => $expired_at->format('d-m-Y')
            ], 200);
        } catch (\Throwable $e) {
            return parent::api_unknown_error();
        }
    }
}
