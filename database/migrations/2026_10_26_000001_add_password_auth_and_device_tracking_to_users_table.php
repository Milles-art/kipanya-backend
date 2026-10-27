<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Password-based authentication and anti-farm signals.
     *
     * - device_hash / signup_ip: recorded when an account is created so the
     *   admin panel can flag several accounts born on one browser or one
     *   connection. Only the HMAC of the first-party device cookie is stored.
     * - reset_token_hash / reset_expires_at: single-use password-reset token
     *   (sha256 of the token carried in the emailed link). The plaintext token
     *   never touches the database.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->char('device_hash', 64)->nullable()->index()->after('email_hash');
            $table->string('signup_ip', 45)->nullable()->after('device_hash');
            $table->char('reset_token_hash', 64)->nullable()->index()->after('signup_ip');
            $table->timestamp('reset_expires_at')->nullable()->after('reset_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['device_hash']);
            $table->dropIndex(['reset_token_hash']);
            $table->dropColumn(['device_hash', 'signup_ip', 'reset_token_hash', 'reset_expires_at']);
        });
    }
};
