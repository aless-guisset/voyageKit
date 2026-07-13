<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    public function test_request_is_treated_as_secure_behind_railways_reverse_proxy(): void
    {
        $this->call('GET', '/up', server: [
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '203.0.113.5',
        ]);

        $this->assertTrue(request()->isSecure());
    }
}
