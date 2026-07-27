<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direct_messages', function (Blueprint $table): void {
            $table->foreignId('reply_to_id')
                ->nullable()
                ->after('recipient_id')
                ->constrained('direct_messages')
                ->nullOnDelete();
            $table->string('message_type', 16)->default('text')->after('reply_to_id');
            $table->string('sticker_key', 32)->nullable()->after('body');
            $table->timestamp('delivered_at')->nullable()->after('sticker_key');
            $table->timestamp('pinned_at')->nullable()->after('read_at');
            $table->foreignId('pinned_by_id')
                ->nullable()
                ->after('pinned_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['recipient_id', 'delivered_at']);
            $table->index(['pinned_at', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('availability_status', 16)
                ->default('available')
                ->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('direct_messages', function (Blueprint $table): void {
            $table->dropForeign(['reply_to_id']);
            $table->dropForeign(['pinned_by_id']);
            $table->dropIndex(['recipient_id', 'delivered_at']);
            $table->dropIndex(['pinned_at', 'created_at']);
            $table->dropColumn([
                'reply_to_id',
                'message_type',
                'sticker_key',
                'delivered_at',
                'pinned_at',
                'pinned_by_id',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('availability_status');
        });
    }
};
