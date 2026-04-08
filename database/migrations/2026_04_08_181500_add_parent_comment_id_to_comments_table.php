<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->foreignId('parent_comment_id')
                ->nullable()
                ->after('post_id')
                ->constrained('comments')
                ->nullOnDelete();

            $table->index(['parent_comment_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_comment_id');
            $table->dropIndex(['parent_comment_id', 'created_at']);
        });
    }
};