<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');
            $table->string('payment_method', 100)->default('BCA Merchant');
            $table->decimal('amount', 12, 2);
            $table->string('proof_file_path')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->enum('verification_status', [
                'Menunggu Verifikasi',
                'Valid',
                'Ditolak'
            ])->default('Menunggu Verifikasi');
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

