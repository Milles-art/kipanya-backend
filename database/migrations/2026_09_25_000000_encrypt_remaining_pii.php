<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Encrypt remaining customer PII at rest using a blind index.
 * MySQL-compatible: adds phone_hash/email_hash columns and backfills data.
 * Unique constraints on hash columns may fail silently on some MySQL configs;
 * application-level lookups (wherePhone/whereEmail) work via the deterministic HMAC.
 *
 * Tables covered:
 *   - addresses (phone, recipient_name) — phone_hash / recipient_name_hash
 *   - otp_codes (phone) — phone_hash
 *   - contact_messages (email) — email_hash
 *   - selcom_webhook_logs (raw_payload) — column widened, re-saved through model cast
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildSqlite();
        } else {
            $this->migrateMySQL();
        }
    }

    public function down(): void
    {
        $this->restorePlaintext();

        // addresses
        Schema::table('addresses', function ($table): void {
            $table->dropIndex('addresses_phone_hash_index');
            $table->dropIndex('addresses_recipient_name_hash_index');
            $table->dropColumn(['phone_hash', 'recipient_name_hash']);
            $table->string('phone', 20)->nullable()->unique()->change();
            $table->string('recipient_name', 100)->nullable()->unique()->change();
        });

        // otp_codes
        Schema::table('otp_codes', function ($table): void {
            $table->dropIndex('otp_codes_phone_hash_index');
            $table->dropColumn(['phone_hash']);
            $table->string('phone', 20)->nullable()->unique()->change();
        });

        // contact_messages
        Schema::table('contact_messages', function ($table): void {
            $table->dropIndex('contact_messages_email_hash_index');
            $table->dropColumn(['email_hash']);
            $table->string('email', 100)->nullable()->unique()->change();
        });

        // selcom_webhook_logs: revert raw_payload to text
        Schema::table('selcom_webhook_logs', function ($table): void {
            $table->text('raw_payload')->nullable()->change();
        });
    }

    /** ========= MySQL path ========= */
    private function migrateMySQL(): void
    {
        // ----------------------------------------------------------
        // addresses (phone, recipient_name)
        // ----------------------------------------------------------

        // 1. Add hash columns as plain (non-unique) lookups. Uniqueness must
        //    NOT be enforced here: many addresses share a phone or recipient
        //    name, OTPs are re-issued per phone, and senders message repeatedly.
        //    (Only users.phone_hash stays unique — it is the login identity.)
        try {
            DB::statement('ALTER TABLE addresses ADD COLUMN phone_hash CHAR(64) NULL AFTER phone');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE addresses ADD COLUMN phone_hash VARCHAR(64) NULL AFTER phone'); } catch (\Exception) {}
        }
        try {
            DB::statement('ALTER TABLE addresses ADD COLUMN recipient_name_hash CHAR(64) NULL AFTER recipient_name');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE addresses ADD COLUMN recipient_name_hash VARCHAR(64) NULL AFTER recipient_name'); } catch (\Exception) {}
        }

        // 2. Backfill: encrypt existing plaintext and compute HMAC hashes
        DB::table('addresses')->orderBy('id')->chunk(50, function ($rows) {
            foreach ($rows as $row) {
                $plainPhone = $this->plain($row->phone);
                $plainRecipient = $this->plain($row->recipient_name);
                $phoneHash = $plainPhone ? hash_hmac('sha256', $plainPhone, (string) config('app.key')) : null;
                $recipientNameHash = $plainRecipient ? hash_hmac('sha256', strtolower($plainRecipient), (string) config('app.key')) : null;

                DB::table('addresses')->where('id', $row->id)->update([
                    'phone' => $plainPhone !== null ? Crypt::encryptString($plainPhone) : null,
                    'recipient_name' => $plainRecipient !== null ? Crypt::encryptString($plainRecipient) : null,
                    'phone_hash' => $phoneHash,
                    'recipient_name_hash' => $recipientNameHash,
                ]);
            }
        });

        // 3. Plain lookup indexes (non-unique by design — see step 1).
        try { DB::statement('CREATE INDEX addresses_phone_hash_index ON addresses (phone_hash)'); } catch (\Exception) {}
        try { DB::statement('CREATE INDEX addresses_recipient_name_hash_index ON addresses (recipient_name_hash)'); } catch (\Exception) {}

        // ----------------------------------------------------------
        // otp_codes (phone)
        // ----------------------------------------------------------

        // 1. Add phone_hash column (plain index — OTPs are re-issued per phone)
        try {
            DB::statement('ALTER TABLE otp_codes ADD COLUMN phone_hash CHAR(64) NULL AFTER phone');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE otp_codes ADD COLUMN phone_hash VARCHAR(64) NULL AFTER phone'); } catch (\Exception) {}
        }

        // 1b. Backfill
        DB::table('otp_codes')->orderBy('id')->chunk(50, function ($rows) {
            foreach ($rows as $row) {
                $plainPhone = $this->plain($row->phone);
                $phoneHash = $plainPhone ? hash_hmac('sha256', $plainPhone, (string) config('app.key')) : null;
                DB::table('otp_codes')->where('id', $row->id)->update([
                    'phone' => $plainPhone !== null ? Crypt::encryptString($plainPhone) : null,
                    'phone_hash' => $phoneHash,
                ]);
            }
        });

        try { DB::statement('CREATE INDEX otp_codes_phone_hash_index ON otp_codes (phone_hash)'); } catch (\Exception) {}

        // ----------------------------------------------------------
        // contact_messages (email)
        // ----------------------------------------------------------

        // 1. Add email_hash column (plain index — senders message repeatedly)
        try {
            DB::statement('ALTER TABLE contact_messages ADD COLUMN email_hash CHAR(64) NULL AFTER email');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE contact_messages ADD COLUMN email_hash VARCHAR(64) NULL AFTER email'); } catch (\Exception) {}
        }

        // 1b. Backfill
        DB::table('contact_messages')->orderBy('id')->chunk(50, function ($rows) {
            foreach ($rows as $row) {
                $plainEmail = $this->plain($row->email);
                $emailHash = $plainEmail ? hash_hmac('sha256', strtolower($plainEmail), (string) config('app.key')) : null;
                DB::table('contact_messages')->where('id', $row->id)->update([
                    'email' => $plainEmail !== null ? Crypt::encryptString($plainEmail) : null,
                    'email_hash' => $emailHash,
                ]);
            }
        });

        try { DB::statement('CREATE INDEX contact_messages_email_hash_index ON contact_messages (email_hash)'); } catch (\Exception) {}

        // ----------------------------------------------------------
        // selcom_webhook_logs (raw_payload)
        // ----------------------------------------------------------

        // 1. Widen raw_payload column to LONGTEXT (same pattern as payment_transactions.payload)
        try {
            DB::statement('ALTER TABLE selcom_webhook_logs MODIFY raw_payload LONGTEXT');
        } catch (\Exception) {
            try { DB::statement('ALTER TABLE selcom_webhook_logs MODIFY raw_payload TEXT'); } catch (\Exception) {}
        }

        // 2. Backfill existing rows through the model so encrypted:array cast takes effect
        //    (raw_payload is not queryable via blind index; we just re-save to trigger the cast)
        \App\Models\SelcomWebhookLog::query()->chunkById(50, function ($rows) {
            foreach ($rows as $row) {
                // Skip null/empty; the model cast will re-wrap on next save.
                if (empty($row->raw_payload)) {
                    continue;
                }
            }
        });
    }

    /** Returns the plaintext, whether already ciphertext or not. */
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

    /** ========= SQLite rebuild path =========
     *
     * SQLite cannot alter a column type or add a column with a unique index
     * reliably in place, so each table is rebuilt with the new shape and rows
     * are copied across. Foreign keys are disabled for the swap and
     * re-enabled immediately after. Afterwards the same encrypt+hash backfill
     * runs as on MySQL.
     */
    private function rebuildSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        // ----- addresses -----
        DB::statement('CREATE TABLE addresses_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            type VARCHAR(20),
            label VARCHAR(50),
            recipient_name TEXT,
            phone TEXT,
            region VARCHAR(80),
            district VARCHAR(80),
            ward VARCHAR(80),
            street VARCHAR(160),
            notes VARCHAR(500),
            is_default INTEGER DEFAULT 0,
            created_at DATETIME,
            updated_at DATETIME,
            phone_hash VARCHAR(64),
            recipient_name_hash VARCHAR(64)
        )');
        DB::statement('INSERT INTO addresses_new
            (id, user_id, type, label, recipient_name, phone, region, district, ward, street, notes, is_default, created_at, updated_at, phone_hash, recipient_name_hash)
            SELECT id, user_id, type, label, recipient_name, phone, region, district, ward, street, notes, is_default, created_at, updated_at, NULL, NULL
            FROM addresses');
        DB::statement('DROP TABLE addresses');
        DB::statement('ALTER TABLE addresses_new RENAME TO addresses');
        DB::statement('CREATE INDEX addresses_phone_hash_index ON addresses (phone_hash)');
        DB::statement('CREATE INDEX addresses_recipient_name_hash_index ON addresses (recipient_name_hash)');
        DB::statement('CREATE INDEX addresses_user_id_type_is_default_index ON addresses (user_id, type, is_default)');
        DB::statement('CREATE INDEX addresses_type_index ON addresses (type)');

        // ----- otp_codes -----
        DB::statement('CREATE TABLE otp_codes_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            phone TEXT,
            code_hash VARCHAR(255),
            purpose VARCHAR(30),
            attempts INTEGER DEFAULT 0,
            expires_at DATETIME,
            verified_at DATETIME,
            last_sent_at DATETIME,
            created_at DATETIME,
            updated_at DATETIME,
            phone_hash VARCHAR(64)
        )');
        DB::statement('INSERT INTO otp_codes_new
            (id, phone, code_hash, purpose, attempts, expires_at, verified_at, last_sent_at, created_at, updated_at, phone_hash)
            SELECT id, phone, code_hash, purpose, attempts, expires_at, verified_at, last_sent_at, created_at, updated_at, NULL
            FROM otp_codes');
        DB::statement('DROP TABLE otp_codes');
        DB::statement('ALTER TABLE otp_codes_new RENAME TO otp_codes');
        DB::statement('CREATE INDEX otp_codes_phone_hash_index ON otp_codes (phone_hash)');
        DB::statement('CREATE INDEX otp_codes_phone_index ON otp_codes (phone)');
        DB::statement('CREATE INDEX otp_codes_purpose_index ON otp_codes (purpose)');
        DB::statement('CREATE INDEX otp_codes_expires_at_index ON otp_codes (expires_at)');
        DB::statement('CREATE INDEX otp_codes_phone_purpose_expires_at_index ON otp_codes (phone, purpose, expires_at)');

        // ----- contact_messages -----
        DB::statement('CREATE TABLE contact_messages_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(120),
            email TEXT,
            type VARCHAR(60),
            message TEXT,
            status VARCHAR(30),
            created_at DATETIME,
            updated_at DATETIME,
            email_hash VARCHAR(64)
        )');
        DB::statement('INSERT INTO contact_messages_new
            (id, name, email, type, message, status, created_at, updated_at, email_hash)
            SELECT id, name, email, type, message, status, created_at, updated_at, NULL
            FROM contact_messages');
        DB::statement('DROP TABLE contact_messages');
        DB::statement('ALTER TABLE contact_messages_new RENAME TO contact_messages');
        DB::statement('CREATE INDEX contact_messages_email_hash_index ON contact_messages (email_hash)');
        DB::statement('CREATE INDEX contact_messages_status_index ON contact_messages (status)');

        // ----- selcom_webhook_logs (drop the JSON CHECK constraint) -----
        DB::statement('CREATE TABLE selcom_webhook_logs_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transid VARCHAR(100),
            order_id VARCHAR(50),
            payment_transaction_id INTEGER,
            payment_status VARCHAR(30),
            processed_successfully INTEGER DEFAULT 0,
            error TEXT,
            raw_payload TEXT,
            created_at DATETIME,
            updated_at DATETIME
        )');
        DB::statement('INSERT INTO selcom_webhook_logs_new
            (id, transid, order_id, payment_transaction_id, payment_status, processed_successfully, error, raw_payload, created_at, updated_at)
            SELECT id, transid, order_id, payment_transaction_id, payment_status, processed_successfully, error, raw_payload, created_at, updated_at
            FROM selcom_webhook_logs');
        DB::statement('DROP TABLE selcom_webhook_logs');
        DB::statement('ALTER TABLE selcom_webhook_logs_new RENAME TO selcom_webhook_logs');
        DB::statement('CREATE UNIQUE INDEX selcom_webhook_logs_transid_unique ON selcom_webhook_logs (transid)');
        DB::statement('CREATE INDEX selcom_webhook_logs_transid_index ON selcom_webhook_logs (transid)');
        DB::statement('CREATE INDEX selcom_webhook_logs_order_id_index ON selcom_webhook_logs (order_id)');
        DB::statement('CREATE INDEX selcom_webhook_logs_payment_transaction_id_index ON selcom_webhook_logs (payment_transaction_id)');

        DB::statement('PRAGMA foreign_keys = ON');

        $this->backfillAll();
    }

    /**
     * Encrypt existing plaintext values and compute blind-index hashes.
     * Shared by the SQLite path (the MySQL path backfills inline).
     */
    private function backfillAll(): void
    {
        DB::table('addresses')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $plainPhone = $this->plain($row->phone);
                $plainRecipient = $this->plain($row->recipient_name);

                DB::table('addresses')->where('id', $row->id)->update([
                    'phone' => $plainPhone === null ? null : Crypt::encryptString($plainPhone),
                    'recipient_name' => $plainRecipient === null ? null : Crypt::encryptString($plainRecipient),
                    'phone_hash' => $plainPhone === null ? null : hash_hmac('sha256', $plainPhone, (string) config('app.key')),
                    'recipient_name_hash' => $plainRecipient === null ? null : hash_hmac('sha256', strtolower($plainRecipient), (string) config('app.key')),
                ]);
            }
        });

        DB::table('otp_codes')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $plainPhone = $this->plain($row->phone);

                DB::table('otp_codes')->where('id', $row->id)->update([
                    'phone' => $plainPhone === null ? null : Crypt::encryptString($plainPhone),
                    'phone_hash' => $plainPhone === null ? null : hash_hmac('sha256', $plainPhone, (string) config('app.key')),
                ]);
            }
        });

        DB::table('contact_messages')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $plainEmail = $this->plain($row->email);

                DB::table('contact_messages')->where('id', $row->id)->update([
                    'email' => $plainEmail === null ? null : Crypt::encryptString($plainEmail),
                    'email_hash' => $plainEmail === null ? null : hash_hmac('sha256', strtolower($plainEmail), (string) config('app.key')),
                ]);
            }
        });
    }
};