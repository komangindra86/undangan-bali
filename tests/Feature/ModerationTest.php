<?php

namespace Tests\Feature;

use App\Models\ContentReport;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_a_comment_once_and_admin_can_hide_it(): void
    {
        $invitation = $this->publishedInvitation();
        $author = User::factory()->create(['role' => 'user']);
        $reporter = User::factory()->create(['role' => 'user']);
        $comment = $invitation->comments()->create(['user_id' => $author->id, 'body' => 'Komentar kasar']);
        $url = "/api/moments/{$invitation->id}/comments/{$comment->id}/report";

        $this->actingAs($reporter, 'sanctum')->postJson($url, ['reason' => 'harassment'])->assertCreated();
        $this->actingAs($reporter, 'sanctum')->postJson($url, ['reason' => 'spam', 'note' => 'Berulang'])->assertCreated();
        $this->actingAs($author, 'sanctum')->postJson($url, ['reason' => 'spam'])->assertUnprocessable();

        $this->assertSame(1, ContentReport::count());
        $report = ContentReport::firstOrFail();
        $this->assertSame('spam', $report->reason);
        $this->assertSame($author->id, $report->reported_user_id);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'web')->get('/admin/reports')->assertOk()->assertSee('Komentar kasar');
        $this->actingAs($admin, 'web')
            ->put("/admin/reports/{$report->id}", ['action' => 'hide_comment'])
            ->assertRedirect();

        $this->assertNotNull($comment->fresh()->deleted_at);
        $this->assertSame('resolved', $report->fresh()->status);
        $this->getJson("/api/moments/{$invitation->id}")->assertJsonCount(0, 'data.comments');
    }

    public function test_admin_can_hide_a_reported_moment_from_the_feed(): void
    {
        $invitation = $this->publishedInvitation();
        $reporter = User::factory()->create(['role' => 'user']);

        $this->actingAs($reporter, 'sanctum')
            ->postJson("/api/moments/{$invitation->id}/report", ['reason' => 'inappropriate'])
            ->assertCreated();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'web')
            ->put('/admin/reports/'.ContentReport::firstOrFail()->id, ['action' => 'hide_moment'])
            ->assertRedirect();

        $this->assertTrue($invitation->fresh()->is_hidden_from_feed);
        $this->getJson('/api/moments')->assertJsonCount(0, 'data');
    }

    public function test_non_admin_cannot_open_the_report_queue(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'web')->get('/admin/reports')->assertForbidden();
    }

    public function test_blocking_hides_content_both_ways_and_stops_interaction(): void
    {
        $invitation = $this->publishedInvitation();
        $owner = $invitation->user;
        $viewer = User::factory()->create(['role' => 'user']);
        $troll = User::factory()->create(['role' => 'user']);
        $invitation->comments()->create(['user_id' => $troll->id, 'body' => 'Komentar troll']);

        $this->actingAs($viewer, 'sanctum')->postJson("/api/users/{$troll->id}/block")->assertOk();
        $this->actingAs($viewer, 'sanctum')->getJson("/api/moments/{$invitation->id}")
            ->assertJsonCount(0, 'data.comments');
        $this->actingAs($viewer, 'sanctum')->getJson('/api/blocked-users')
            ->assertJsonPath('data.0.id', $troll->id);

        $this->actingAs($owner, 'sanctum')->postJson("/api/users/{$troll->id}/block")->assertOk();
        $this->actingAs($troll, 'sanctum')->getJson('/api/moments')->assertJsonCount(0, 'data');
        $this->actingAs($troll, 'sanctum')->getJson("/api/moments/{$invitation->id}")->assertNotFound();
        $this->actingAs($troll, 'sanctum')
            ->postJson("/api/moments/{$invitation->id}/comments", ['body' => 'Masih di sini'])
            ->assertForbidden();
        $this->actingAs($troll, 'sanctum')
            ->postJson("/api/moments/{$invitation->id}/reaction", ['type' => 'like'])
            ->assertForbidden();

        $this->actingAs($owner, 'sanctum')->deleteJson("/api/users/{$troll->id}/block")->assertOk();
        $this->actingAs($troll, 'sanctum')->getJson('/api/moments')->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_block_themselves(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'sanctum')->postJson("/api/users/{$user->id}/block")->assertUnprocessable();
    }

    private function publishedInvitation(): Invitation
    {
        $owner = User::factory()->create(['role' => 'user']);
        $template = InvitationTemplate::create([
            'name' => 'Bali Classic',
            'slug' => 'bali-classic',
            'blade_view' => 'invitations.templates.bali-classic',
        ]);

        return Invitation::create([
            'user_id' => $owner->id,
            'template_id' => $template->id,
            'slug' => 'wira-ayu',
            'status' => 'published',
            'groom_full_name' => 'I Made Wira Adnyana',
            'groom_nickname' => 'Wira',
            'bride_full_name' => 'Ni Putu Ayu Lestari',
            'bride_nickname' => 'Ayu',
            'event_date' => now()->addMonth()->toDateString(),
            'published_at' => now(),
        ]);
    }
}
