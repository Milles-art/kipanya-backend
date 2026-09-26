<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Encrypt pre-existing plaintext webhook payloads at rest.
 *
 * The `raw_payload` column was widened to LONGTEXT earlier, and the model
 * now casts it as `encrypted:array`. Rows written before that change still
 * hold plaintext JSON, which the new cast cannot read — so re-wrap them
 * here. Idempotent: rows that already decrypt are skipped.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('selcom_webhook_logs')
            ->whereNotNull('raw_payload')
            ->orderBy('id')
            ->chunk(100, function ($rows): void {
                foreach ($rows as $row) {
                    try {
                        Crypt::decryptString((string) $row->raw_payload);

                        continue;
                    } catch (\Throwable) {
                        // Plaintext — encrypt below.
                    }

                    DB::table('selcom_webhook_logs')
                        ->where('id', $row->id)
                        ->update(['raw_payload' => Crypt::encryptString((string) $row->raw_payload)]);
                }
            });
    }

    public function down(): void
    {
        DB::table('selcom_webhook_logs')
            ->whereNotNull('raw_payload')
            ->orderBy('id')
            ->chunk(100, function ($rows): void {
                foreach ($rows as $row) {
                    try {
                        $plain = Crypt::decryptString((string) $row->raw_payload);
                    } catch (\Throwable) {
                        continue;
                    }

                    DB::table('selcom_webhook_logs')
                        ->where('id', $row->id)
                        ->update(['raw_payload' => $plain]);
                }
            });
    }
};
