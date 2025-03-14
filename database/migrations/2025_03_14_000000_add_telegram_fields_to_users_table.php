<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('users', 'telegramId')) {
                // Add telegramId after remember_token
                $table->string('telegramId')->nullable()
                    ->after('remember_token')
                    ->comment('Telegram user identifier');
            }

            if (!Schema::hasColumn('users', 'telegram_avatar_url')) {
                // Add avatar_url if it doesn't exist
                $table->string('telegram_avatar_url')->nullable()
                    ->after('telegramId')
                    ->comment('URL to user telegram avatar image');
            }

            if (!Schema::hasColumn('users', 'telegram_photo_path')) {
                // Add profile_photo_path if it doesn't exist
                $table->string('telegram_photo_path')->nullable()
                    ->after('telegram_avatar_url')
                    ->comment('Local path to user profile photo');
            }
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only drop columns if they exist
            if (Schema::hasColumn('users', 'telegramId')) {
                $table->dropColumn('telegramId');
            }

            if (Schema::hasColumn('users', 'telegram_avatar_url')) {
                $table->dropColumn('telegram_avatar_url');
            }

            if (Schema::hasColumn('users', 'telegram_photo_path')) {
                $table->dropColumn('telegram_photo_path');
            }
        });
    }
};
