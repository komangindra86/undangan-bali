<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitation_comments', function (Blueprint $table) {
            $table->string('client_request_id', 80)->nullable()->after('body')->unique();
        });

        $lastCommentByContent = [];
        foreach (DB::table('invitation_comments')->whereNull('deleted_at')->orderBy('id')->cursor() as $comment) {
            $signature = hash('sha256', implode('|', [
                $comment->invitation_id,
                $comment->user_id,
                preg_replace('/\s+/', ' ', trim($comment->body)),
            ]));
            $createdAt = strtotime($comment->created_at);
            $lastCreatedAt = $lastCommentByContent[$signature] ?? null;

            if ($lastCreatedAt !== null && $createdAt - $lastCreatedAt <= 60) {
                DB::table('invitation_comments')->where('id', $comment->id)->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

                continue;
            }

            $lastCommentByContent[$signature] = $createdAt;
        }
    }

    public function down(): void
    {
        Schema::table('invitation_comments', function (Blueprint $table) {
            $table->dropUnique(['client_request_id']);
            $table->dropColumn('client_request_id');
        });
    }
};
