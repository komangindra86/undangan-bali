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
        config()->set('services.custom_invitation.whatsapp_number', '6281234567890');

        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('Cerita cinta Bali')
            ->assertSee('Coba tanpa login')
            ->assertSee('Wedding Gift')
            ->assertSee('Mulai sendiri secara gratis')
            ->assertSee('Konsultasi Custom via WhatsApp')
            ->assertSee('https://wa.me/6281234567890?text=', false)
            ->assertSee('Download di Google Play')
            ->assertSee('https://play.google.com/store/apps/details?id=com.balisantih.undanganbali', false);
    }

    public function test_privacy_policy_page_is_available(): void
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Kebijakan Privasi Undangan Bali Santih')
            ->assertSee('Midtrans Server Key hanya disimpan di backend Laravel')
            ->assertSee('Aplikasi mobile tidak menyediakan checkout')
            ->assertSee('usia opsional')
            ->assertSee('tidak otomatis masuk feed')
            ->assertSee('orang tua atau wali')
            ->assertSee('Xendit')
            ->assertSee('Firebase Cloud Messaging')
            ->assertSee('Login Google')
            ->assertSee('id="penghapusan-akun"', false)
            ->assertSee('Kirim Permintaan Penghapusan')
            ->assertSee('Permintaan diproses manual')
            ->assertSee('mailto:admin.balisantih@gmail.com?subject=', false)
            ->assertDontSee('atau notifikasi push untuk MVP ini');
    }

    public function test_terms_of_service_page_is_available(): void
    {
        $this->get('/terms-of-service')
            ->assertOk()
            ->assertSee('Syarat dan Ketentuan Undangan Bali Santih')
            ->assertSee('Konten Pengguna')
            ->assertSee('Musik Bawaan')
            ->assertSee('katalog Pixabay')
            ->assertSee('pengguna menyatakan memiliki hak atau izin')
            ->assertSee('tidak boleh mengunduh, menjual, mendaftarkan ke Content ID')
            ->assertSee('Gift dan Pembayaran');
    }
}
