<?php

namespace Tests\Feature;

use App\Models\Token;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TokenTest extends TestCase
{
    use RefreshDatabase;


    public function test_can_generate_token()
    {
        $response = $this->post('/api/token');

        $response->assertJsonStructure([
            'token',
            'expired_at'
        ]);
    }

    public function test_generated_token_are_valid()
    {
        $check = false;
        $token = json_decode($this->post('/api/token')->content(), true)['token'];

        $tokens = Token::where('expired_at', '>', Carbon::now())->get();

        foreach ($tokens as $_token) {
            if (Hash::check($token, $_token->token)) {
                $check = true;
                break;
            }
        }

        $this->assertTrue($check);
    }
}
