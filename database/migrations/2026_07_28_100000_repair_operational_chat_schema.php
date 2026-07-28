<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('direct_messages')) {
            return;
        }

        $this->addMissingMessageColumns();
        $this->addMissingMessageIndexes();

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'availability_status')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('availability_status', 16)
                    ->default('available')
                    ->after('last_seen_at');
            });
        }
    }

    public function down(): void
    {
        // Repairs partially restored databases and is intentionally irreversible.
    }

    private function addMissingMessageColumns(): void
    {
        if (! Schema::hasColumn('direct_messages', 'reply_to_id')) {
            Schema::table('direct_messages', function (Blueprint $table): void {
                $table->foreignId('reply_to_id')
                    ->nullable()
                    ->after('recipient_id')
                    ->constrained('direct_messages')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('direct_messages', 'message_type')) {
            Schema::table('direct_messages', function (Blueprint $table): void {
                $table->string('message_type', 16)
                    ->default('text')
                    ->after('reply_to_id');
            });
        }

        if (! Schema::hasColumn('direct_messages', 'sticker_key')) {
            Schema::table('direct_messages', function (Blueprint $table): void {
                $table->string('sticker_key', 32)
                    ->nullable()
                    ->after('body');
            });
        }

        if (! Schema::hasColumn('direct_messages', 'delivered_at')) {
            Schema::table('direct_messages', function (Blueprint $table): void {
                $table->timestamp('delivered_at')
                    ->nullable()
                    ->after('sticker_key');
            });
        }

        if (! Schema::hasColumn('direct_messages', 'pinned_at')) {
            Schema::table('direct_messages', function (Blueprint $table): void {
                $table->timestamp('pinned_at')
                    ->nullable()
                    ->after('read_at');
            });
        }

        if (! Schema::hasColumn('direct_messages', 'pinned_by_id')) {
            Schema::table('direct_messages', function (Blueprint $table): void {
                $table->foreignId('pinned_by_id')
                    ->nullable()
                    ->after('pinned_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    private function addMissingMessageIndexes(): void
    {
        if (! Schema::hasIndex('direct_messages', 'direct_messages_recipient_id_delivered_at_index')) {
            Schema::table('direct_messages', function (Blueprint $table): void {
                $table->index(['recipient_id', 'delivered_at']);
            });
        }

        if (! Schema::hasIndex('direct_messages', 'direct_messages_pinned_at_created_at_index')) {
            Schema::table('direct_messages', function (Blueprint $table): void {
                $table->index(['pinned_at', 'created_at']);
            });
        }
    }
};
