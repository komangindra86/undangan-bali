<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();
            $table->string('preview_image')->nullable();
            $table->string('blade_view')->default('invitations.templates.bali-classic');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_templates');
    }
};
