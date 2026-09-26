<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widen PII columns that still cannot hold ciphertext.
 *
 * The remaining-PII migration added blind-index hash columns and backfilled
 * rows that existed at the time, but never widened `otp_codes.phone`
 * (varchar(20)) or `contact_messages.email` (varchar(160)). Any write of an
 * encrypted value to those columns fails with "Data too long", which breaks
 * OTP issuance (registration, login, contact changes) and the contact form.
 * Fresh SQLite builds already get TEXT columns from the rebuild path there,
 * so this migration only alters MySQL — then backfills any plaintext rows
 * left behind on either driver.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // otp_codes.phone still carries indexes from its create migration,
            // and MySQL cannot widen an indexed varchar to TEXT (key length
            // limit). Drop the phone-covering indexes first; lookups now go
            // through phone_hash, and (purpose, expires_at) keeps its index.
            $this->dropIndexIfExists('otp_codes', 'otp_codes_phone_index');
            $this->dropIndexIfExists('otp_codes', 'otp_codes_phone_purpose_expires_at_index');
            DB::statement('ALTER TABLE otp_codes MODIFY phone TEXT');
            $this->createIndexIfMissing(
                'otp_codes',
                'otp_codes_purpose_expires_at_index',
                '(purpose, expires_at)'
            );

            DB::statement('ALTER TABLE contact_messages MODIFY email TEXT');
        }

        $this->backfill();
    }

    public function down(): void
    {
        // Best-effort restore of plaintext; column types are left widened on
        // purpose so a rollback never strands ciphertext in narrow columns.
        $this->restorePlaintext();
    }

    private function backfill(): void
    {
        DB::table('otp_codes')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $plain = $this->plain($row->phone);

                DB::table('otp_codes')->where('id', $row->id)->update([
                    'phone' => $plain === null ? null : Crypt::encryptString($plain),
                    'phone_hash' => $plain === null ? null : hash_hmac('sha256', $plain, (string) config('app.key')),
                ]);
            }
        });

        DB::table('contact_messages')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $plain = $this->plain($row->email);

                DB::table('contact_messages')->where('id', $row->id)->update([
                    'email' => $plain === null ? null : Crypt::encryptString($plain),
                    'email_hash' => $plain === null ? null : hash_hmac('sha256', strtolower($plain), (string) config('app.key')),
                ]);
            }
        });
    }

    private function restorePlaintext(): void
    {
        DB::table('otp_codes')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $plain = $this->plain($row->phone);

                DB::table('otp_codes')->where('id', $row->id)->update([
                    'phone' => $plain,
                    'phone_hash' => $plain === null ? null : hash_hmac('sha256', $plain, (string) config('app.key')),
                ]);
            }
        });

        DB::table('contact_messages')->orderBy('id')->chunk(100, function ($rows): void {
            foreach ($rows as $row) {
                $plain = $this->plain($row->email);

                DB::table('contact_messages')->where('id', $row->id)->update([
                    'email' => $plain,
                    'email_hash' => $plain === null ? null : hash_hmac('sha256', strtolower($plain), (string) config('app.key')),
                ]);
            }
        });
    }

    private function indexNames(string $table): array
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->all();
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (in_array($index, $this->indexNames($table), true)) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }

    private function createIndexIfMissing(string $table, string $index, string $columns): void
    {
        if (! in_array($index, $this->indexNames($table), true)) {
            DB::statement("CREATE INDEX `{$index}` ON `{$table}` {$columns}");
        }
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
};
