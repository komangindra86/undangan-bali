<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('comment_id')->nullable()->constrained('invitation_comments')->cascadeOnDelete();
            $table->string('reason', 30);
            $table->string('note', 500)->nullable();
            $table->string('status', 20)->default('open');
            $table->string('admin_note', 500)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['invitation_id', 'comment_id', 'status']);
        });

        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blocker_user_id', 'blocked_user_id']);
            $table->index('blocked_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('content_reports');
    }
};
