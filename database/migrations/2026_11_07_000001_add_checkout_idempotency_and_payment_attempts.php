<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('checkout_token')->nullable()->unique('orders_checkout_token_unique');
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number');
            $table->decimal('amount', 12, 2);
            $table->string('proof_file_path')->nullable();
            $table->string('verification_status', 32)->default('Menunggu Verifikasi');
            $table->text('admin_notes')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['payment_id', 'attempt_number'], 'payment_attempts_payment_number_unique');
            $table->index(['payment_id', 'verification_status'], 'payment_attempts_payment_status_index');
        });

        $now = now();
        DB::table('payments')
            ->whereNotNull('proof_file_path')
            ->orderBy('id')
            ->chunkById(100, function ($payments) use ($now) {
                foreach ($payments as $payment) {
                    DB::table('payment_attempts')->insert([
                        'payment_id' => $payment->id,
                        'attempt_number' => 1,
                        'amount' => $payment->amount,
                        'proof_file_path' => $payment->proof_file_path,
                        'verification_status' => $payment->verification_status,
                        'admin_notes' => $payment->admin_notes,
                        'uploaded_at' => $payment->uploaded_at,
                        'verified_by' => $payment->verified_by,
                        'verified_at' => $payment->verified_at,
                        'created_at' => $payment->created_at ?? $now,
                        'updated_at' => $payment->updated_at ?? $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_checkout_token_unique');
            $table->dropColumn('checkout_token');
        });
    }
};
