<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->boolean('is_hidden_from_feed')->default(false)->after('media_deleted_at');
            $table->text('moment_caption')->nullable()->after('is_hidden_from_feed');
            $table->index(['status', 'is_hidden_from_feed', 'published_at']);
        });

        Schema::create('invitation_moments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('body')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['invitation_id', 'occurred_at']);
        });

        Schema::create('invitation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requester_name', 80);
            $table->string('requester_whatsapp', 24);
            $table->string('status', 20)->default('pending');
            $table->timestamp('shared_at')->nullable();
            $table->timestamps();

            $table->index(['invitation_id', 'status']);
            $table->index(['requester_whatsapp', 'created_at']);
        });

        Schema::create('invitation_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['like', 'love']);
            $table->timestamps();

            $table->unique(['invitation_id', 'user_id']);
        });

        Schema::create('invitation_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('body', 500);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['invitation_id', 'created_at']);
        });

        Schema::create('social_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_notifications');
        Schema::dropIfExists('invitation_comments');
        Schema::dropIfExists('invitation_reactions');
        Schema::dropIfExists('invitation_requests');
        Schema::dropIfExists('invitation_moments');

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex(['status', 'is_hidden_from_feed', 'published_at']);
            $table->dropColumn(['is_hidden_from_feed', 'moment_caption']);
        });
    }
};
