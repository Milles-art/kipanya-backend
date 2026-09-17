<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wear_return_requests', function (Blueprint $table): void {
            $table->decimal('refund_amount', 12, 2)->nullable()->after('notes');
            $table->string('refund_status', 30)->nullable()->after('refund_amount')->index();
            $table->string('refund_reference', 120)->nullable()->after('refund_status');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->timestamp('received_at')->nullable()->after('approved_at');
            $table->timestamp('processed_at')->nullable()->after('received_at');
            $table->timestamp('refunded_at')->nullable()->after('processed_at');
            $table->timestamp('completed_at')->nullable()->after('refunded_at');
        });

        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->dropColumn('refunded_amount');
        });

        Schema::table('wear_return_requests', function (Blueprint $table): void {
            $table->dropColumn([
                'refund_amount', 'refund_status', 'refund_reference',
                'approved_at', 'received_at', 'processed_at', 'refunded_at', 'completed_at',
            ]);
        });
    }
};
