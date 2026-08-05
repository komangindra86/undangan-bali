<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('Cerita cinta Bali')
            ->assertSee('Coba tanpa login')
            ->assertSee('Wedding Gift')
            ->assertSee('Download di Google Play')
            ->assertSee('https://play.google.com/store/apps/details?id=com.balisantih.undanganbali', false);
    }

    public function test_privacy_policy_page_is_available(): void
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Kebijakan Privasi Undangan Pernikahan Bali')
            ->assertSee('Midtrans Server Key hanya disimpan di backend Laravel')
            ->assertSee('Aplikasi mobile tidak menyediakan checkout');
    }
}
