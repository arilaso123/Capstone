<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cancellation_requests', function (Blueprint $table) {
            $table->decimal('refund_amount', 12, 2)->nullable()->after('admin_notes');
            $table->string('refund_reference', 100)->nullable()->unique('cancellation_refund_reference_unique')->after('refund_amount');
            $table->string('refund_proof_path')->nullable()->after('refund_reference');
            $table->timestamp('refunded_at')->nullable()->after('refund_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('cancellation_requests', function (Blueprint $table) {
            $table->dropUnique('cancellation_refund_reference_unique');
            $table->dropColumn(['refund_amount', 'refund_reference', 'refund_proof_path', 'refunded_at']);
        });
    }
};
