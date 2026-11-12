<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuestFormThrottleTest extends TestCase
{
    public function test_kontak_form_is_throttled_after_ten_requests_per_minute(): void
    {
        $payload = [
            'nama' => 'Tester',
            'email' => 'tester@example.com',
            'pesan' => 'Pesan uji throttle guest.',
        ];

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('guest.kontak.send'), $payload)
                ->assertRedirect();
        }

        $this->post(route('guest.kontak.send'), $payload)
            ->assertStatus(429);
    }
}
