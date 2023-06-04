<?php

namespace App\Http\Middleware;

use App\Models\Token;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ValidateToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $request_token = $request->bearerToken();

        // No token sent on request
        if (!$request_token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tokens = Token::where('expired_at', '>', Carbon::now())->get();

        foreach ($tokens as $_token) {
            if (Hash::check($request_token, $_token->token)) {
                $token = $_token;
                break;
            }
        }

        // Token not found on database
        if (!isset($token)) {
            return response()->json(['message' => 'Invalid token'], 401);
        } else {
            // Token is expired
            if ($token->is_expired()) {
                return response()->json([
                    'message' => 'Token expired'
                ], 401);
            } else {
                return $next($request);
            }
        }
    }
}
