<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\PostgresTestCase;

class SecurityHeadersTest extends PostgresTestCase
{
    public function test_public_and_authenticated_pages_send_security_headers(): void
    {
        $responses = [$this->get('/'), $this->get('/login')];
        $this->actingAs(User::where('role', 'user')->firstOrFail());
        $responses[] = $this->get('/dashboard');

        foreach ($responses as $response) {
            $response->assertOk()
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeaderMissing('Strict-Transport-Security');
        }
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
