<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Encrypt customer PII at rest using a blind index.
 *
 * Phone and email are the login identity, so simply casting them to
 * `encrypted` is not possible: Eloquent's encrypted cast is non-deterministic
 * (random IV per write), which makes the existing UNIQUE constraints on
 * users.phone / users.email unenforceable and every `where('phone', ...)`
 * lookup unresolvable against ciphertext.
 *
 * The blind-index pattern solves both:
 *   - `phone` / `email`            -> ciphertext (unreadable from a DB dump)
 *   - `phone_hash` / `email_hash`  -> deterministic HMAC-SHA256 keyed with
 *     APP_KEY, so lookups and uniqueness still work without revealing the
 *     plaintext. The User model mirrors this on every write.
 *
 * A substring LIKE search across PII is no longer possible (hashes preserve
 * neither order nor substrings), so admin search falls back to exact-match on
 * the hash plus the still-plaintext `name` column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildSqlite();
        } else {
            Schema::table('users', function ($table): void {
                $table->dropUnique(['phone']);
                $table->dropUnique(['email']);

                $table->longText('phone')->nullable()->change();
                $table->longText('email')->nullable()->change();

                $table->char('phone_hash', 64)->nullable()->unique()->after('phone');
                $table->char('email_hash', 64)->nullable()->unique()->after('email');
            });
        }

        $this->backfill();
    }

    public function down(): void
    {
        $this->restorePlaintext();

        Schema::table('users', function ($table): void {
            $table->dropUnique(['phone_hash']);
            $table->dropUnique(['email_hash']);
            $table->dropColumn(['phone_hash', 'email_hash']);

            $table->string('phone', 20)->nullable()->unique()->change();
            $table->string('email')->unique()->change();
        });
    }

    private function backfill(): void
    {
        DB::table('users')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $plainPhone = $this->plain($row->phone);
                $plainEmail = $this->plain($row->email);

                DB::table('users')->where('id', $row->id)->update([
                    'phone' => $plainPhone === null ? null : Crypt::encryptString($plainPhone),
                    'email' => $plainEmail === null ? null : Crypt::encryptString($plainEmail),
                    'phone_hash' => $plainPhone === null ? null : $this->hash($plainPhone),
                    'email_hash' => $plainEmail === null ? null : $this->hash(strtolower($plainEmail)),
                ]);
            }
        });
    }

    private function restorePlaintext(): void
    {
        DB::table('users')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $phone = $row->phone === null ? null : Crypt::decryptString($row->phone);
                $email = $row->email === null ? null : Crypt::decryptString($row->email);

                DB::table('users')->where('id', $row->id)->update([
                    'phone' => $phone,
                    'email' => $email,
                ]);
            }
        });
    }

    /** Returns the plaintext, whether the value is already ciphertext or not. */
    private function plain(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function hash(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    /**
     * SQLite cannot alter a column type or drop a unique index in place, so
     * the users table is rebuilt with the new shape and rows are copied across.
     * Foreign keys are disabled for the swap and re-enabled immediately after.
     */
    private function rebuildSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        $existing = DB::getSchemaBuilder()->getColumnListing('users');

        // Build the SELECT list from the columns that actually exist, so the
        // migration works both before and after later identity migrations run.
        $pick = static fn (string $name, string $fallback): string => in_array($name, $existing, true)
            ? $name
            : $fallback;

        DB::statement("CREATE TABLE users_pii_temp (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email TEXT,
            email_verified_at DATETIME,
            phone TEXT,
            phone_hash VARCHAR(64),
            email_hash VARCHAR(64),
            password VARCHAR(255),
            remember_token VARCHAR(100),
            two_factor_secret TEXT,
            two_factor_enabled_at DATETIME,
            phone_verified_at DATETIME,
            onboarding_completed_at DATETIME,
            role VARCHAR(20) DEFAULT 'user',
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME,
            updated_at DATETIME
        )");

        $select = [
            'id',
            $pick('name', "'KP Wear'"),
            $pick('email', 'NULL'),
            $pick('email_verified_at', 'NULL'),
            $pick('phone', 'NULL'),
            'NULL AS phone_hash',
            'NULL AS email_hash',
            $pick('password', 'NULL'),
            $pick('remember_token', 'NULL'),
            $pick('two_factor_secret', 'NULL'),
            $pick('two_factor_enabled_at', 'NULL'),
            $pick('phone_verified_at', 'NULL'),
            $pick('onboarding_completed_at', 'NULL'),
            $pick('role', "'user'"),
            $pick('status', "'active'"),
            $pick('created_at', 'NULL'),
            $pick('updated_at', 'NULL'),
        ];

        DB::statement('INSERT INTO users_pii_temp SELECT '.implode(', ', $select).' FROM users');

        DB::statement('DROP TABLE users');
        DB::statement('ALTER TABLE users_pii_temp RENAME TO users');

        DB::statement('CREATE UNIQUE INDEX users_phone_hash_unique ON users (phone_hash)');
        DB::statement('CREATE UNIQUE INDEX users_email_hash_unique ON users (email_hash)');
        DB::statement('CREATE INDEX users_status_index ON users (status)');
        DB::statement('CREATE INDEX users_role_index ON users (role)');
        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');
        DB::statement('CREATE UNIQUE INDEX users_phone_unique ON users (phone)');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
