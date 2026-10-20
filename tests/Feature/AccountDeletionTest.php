<?php

namespace Tests\Feature;

use App\Models\AccountDeletionRequest;
use App\Models\GiftPayoutRequest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\WeddingGift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_deletes_an_invitation_without_gifts_for_good_even_when_live(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create(['role' => 'user']);
        $invitation = $this->invitation($owner, 'wira-ayu');
        Storage::disk('public')->put('invitations/photos/groom.jpg', 'x');
        $invitation->update(['groom_photo' => 'invitations/photos/groom.jpg']);

        $this->actingAs($owner, 'sanctum')->deleteJson("/api/invitations/{$invitation->id}")
            ->assertOk()->assertJsonPath('result', 'deleted');

        $this->assertNull(Invitation::find($invitation->id));
        Storage::disk('public')->assertMissing('invitations/photos/groom.jpg');
        $this->app['auth']->forgetGuards();
        $this->get('/u/wira-ayu')->assertNotFound();
    }

    public function test_an_invitation_with_gift_records_is_hidden_and_its_records_kept(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create(['role' => 'user']);
        $invitation = $this->invitation($owner, 'wira-ayu');
        Storage::disk('public')->put('invitations/gallery/one.jpg', 'x');
        $invitation->update(['gallery_photos' => ['invitations/gallery/one.jpg']]);
        $gift = $this->paidGift($invitation, 100000);

        // While money is still waiting to be paid out the invitation cannot go.
        $this->actingAs($owner, 'sanctum')->deleteJson("/api/invitations/{$invitation->id}")->assertUnprocessable();
        $this->assertNull($invitation->fresh()->owner_deleted_at);

        $this->paidOut($owner, $invitation, $gift);
        $this->actingAs($owner, 'sanctum')->deleteJson("/api/invitations/{$invitation->id}")
            ->assertOk()->assertJsonPath('result', 'hidden');

        $invitation->refresh();
        $this->assertNotNull($invitation->owner_deleted_at);
        $this->assertNull($invitation->gallery_photos);
        Storage::disk('public')->assertMissing('invitations/gallery/one.jpg');
        $this->assertSame(1, WeddingGift::count());
        $this->assertSame(1, GiftPayoutRequest::count());
        $this->actingAs($owner, 'sanctum')->getJson('/api/invitations')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($owner, 'sanctum')->getJson("/api/invitations/{$invitation->id}")->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->get('/u/wira-ayu')->assertStatus(410);
        $this->get('/gift/wira-ayu')->assertNotFound();
    }

    public function test_owner_deletes_their_account_after_agreeing_the_balance_lapses(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create(['role' => 'user', 'email' => 'wira@gmail.com']);
        $owner->payoutAccount()->create(['bank_code' => 'BCA', 'bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder_name' => 'Wira']);
        $kept = $this->invitation($owner, 'wira-ayu');
        $this->paidGift($kept, 150000);
        $gone = $this->invitation($owner, 'wira-ayu-draft');
        $other = User::factory()->create(['role' => 'user']);
        $this->invitation($other, 'orang-lain');
        $kept->comments()->create(['user_id' => $owner->id, 'body' => 'Komentar saya']);
        $owner->createToken('expo-mobile');

        $this->actingAs($owner, 'sanctum')->getJson('/api/account/deletion')->assertOk()
            ->assertJsonPath('data.invitations', 2)
            ->assertJsonPath('data.invitations_with_gifts', 1)
            ->assertJsonPath('data.unpaid_balance', 150000);

        $this->actingAs($owner, 'sanctum')->deleteJson('/api/account', ['confirm' => 'hapus'])->assertJsonValidationErrors('confirm');
        $this->actingAs($owner, 'sanctum')->deleteJson('/api/account', ['confirm' => 'HAPUS'])->assertJsonValidationErrors('forfeit_balance');
        $this->assertNull($owner->fresh()->account_deleted_at);

        $this->actingAs($owner, 'sanctum')->deleteJson('/api/account', ['confirm' => 'HAPUS', 'forfeit_balance' => true])->assertOk();

        $owner->refresh();
        $this->assertNotNull($owner->account_deleted_at);
        $this->assertSame('Akun dihapus', $owner->name);
        $this->assertStringEndsWith('@akun-dihapus.invalid', $owner->email);
        $this->assertSame(0, $owner->tokens()->count());
        $this->assertNull($owner->payoutAccount);
        $this->assertNull(Invitation::find($gone->id));
        $this->assertNotNull($kept->fresh()->owner_deleted_at);
        $this->assertSame(1, WeddingGift::count());
        $this->assertSame(0, $kept->comments()->count());
        $this->assertSame(1, Invitation::where('slug', 'orang-lain')->count());

        // Signing in with Google again makes a fresh, empty account rather than reviving the old one.
        $this->assertSame(0, User::where('email', 'wira@gmail.com')->count());
    }

    public function test_website_takes_deletion_requests_and_admin_handles_them(): void
    {
        $owner = User::factory()->create(['role' => 'user', 'email' => 'pemilik@mail.com', 'name' => 'Pemilik Lama']);
        $this->invitation($owner, 'pemilik-lama');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get('/hapus-akun')->assertOk()->assertSee('Hapus akun')->assertSee('Profil');
        $this->get('/privacy-policy')->assertOk()->assertSee('/hapus-akun');
        $this->post('/hapus-akun', ['email' => 'Pemilik@Mail.com', 'note' => 'Sudah tidak dipakai'])->assertRedirect('/hapus-akun');
        $this->post('/hapus-akun', ['email' => 'bot@example.com', 'website' => 'http://spam.test'])->assertRedirect('/hapus-akun');
        $this->assertSame(['pemilik@mail.com'], AccountDeletionRequest::pluck('email')->all());

        $this->actingAs($admin, 'web')->get('/admin/users')->assertOk()
            ->assertSee('Permintaan hapus akun dari website (1)')->assertSee('Akun ditemukan: Pemilik Lama');

        // An owner without a Google address gets one set by the admin, so they can sign in with Google.
        $this->actingAs($admin, 'web')->put("/admin/users/{$owner->id}/email", ['email' => $admin->email])->assertSessionHasErrors('email');
        $this->actingAs($admin, 'web')->put("/admin/users/{$owner->id}/email", ['email' => ' Pemilik.Baru@Gmail.com '])->assertRedirect();
        $this->assertSame('pemilik.baru@gmail.com', $owner->fresh()->email);

        $this->actingAs($admin, 'web')->delete("/admin/users/{$owner->id}", ['confirm' => 'ya'])->assertSessionHasErrors('confirm');
        $this->actingAs($admin, 'web')->delete("/admin/users/{$owner->id}", ['confirm' => 'HAPUS'])->assertRedirect();
        $this->assertNotNull($owner->fresh()->account_deleted_at);
        $this->assertSame(0, Invitation::where('slug', 'pemilik-lama')->count());

        $request = AccountDeletionRequest::firstOrFail();
        $this->actingAs($admin, 'web')->put("/admin/account-deletion-requests/{$request->id}")->assertRedirect();
        $this->assertSame('dismissed', $request->fresh()->status);
        $this->actingAs($admin, 'web')->get('/admin/users')->assertDontSee('Permintaan hapus akun dari website');
    }

    public function test_only_admins_manage_users_and_admins_cannot_be_deleted_here(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($owner, 'web')->get('/admin/users')->assertForbidden();
        $this->actingAs($owner, 'web')->delete("/admin/users/{$owner->id}", ['confirm' => 'HAPUS'])->assertForbidden();
        $this->actingAs($admin, 'web')->delete("/admin/users/{$admin->id}", ['confirm' => 'HAPUS'])->assertNotFound();
        $this->assertNull($owner->fresh()->account_deleted_at);
        $this->assertNull($admin->fresh()->account_deleted_at);
    }

    private function invitation(User $owner, string $slug): Invitation
    {
        $template = InvitationTemplate::firstOrCreate(['slug' => 'bali-classic'], [
            'name' => 'Bali Classic',
            'blade_view' => 'invitations.templates.bali-classic',
        ]);

        return Invitation::create([
            'user_id' => $owner->id,
            'template_id' => $template->id,
            'slug' => $slug,
            'status' => 'published',
            'groom_full_name' => 'I Made Wira Adnyana',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'event_type' => 'Pawiwahan',
            'event_date' => now()->addMonth()->toDateString(),
            'start_time' => '10:00',
            'venue_name' => 'Bale Banjar Ubud',
            'venue_address' => 'Ubud, Bali',
            'published_at' => now(),
        ]);
    }

    private function paidGift(Invitation $invitation, int $amount): WeddingGift
    {
        $invitation->giftSetting()->updateOrCreate([], ['is_active' => true, 'receiver_name' => 'Wira & Ayu', 'minimum_amount' => 10000]);

        return $invitation->weddingGifts()->create([
            'guest_name' => 'Tamu Baik',
            'gift_amount' => $amount,
            'service_fee' => 0,
            'total_amount' => $amount,
            'order_id' => 'WGIFT-'.$invitation->id.'-'.$amount,
            'transaction_status' => 'paid',
            'paid_at' => now()->subDay(),
        ]);
    }

    private function paidOut(User $owner, Invitation $invitation, WeddingGift $gift): void
    {
        $payout = GiftPayoutRequest::create([
            'user_id' => $owner->id,
            'invitation_id' => $invitation->id,
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'Wira',
            'amount' => $gift->gift_amount,
            'platform_fee' => 1000,
            'net_amount' => $gift->gift_amount - 1000,
            'status' => 'paid',
            'requested_at' => now(),
            'paid_at' => now(),
        ]);
        $payout->items()->create(['wedding_gift_id' => $gift->id, 'amount' => $gift->gift_amount]);
    }
}
