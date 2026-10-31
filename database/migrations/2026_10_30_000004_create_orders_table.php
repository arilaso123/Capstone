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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 64)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->foreignId('po_period_id')->constrained('po_periods')->onDelete('restrict');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'Menunggu Pembayaran',
                'Pembayaran Diverifikasi',
                'Diproses',
                'Barang Dikirim',
                'Selesai',
                'Dibatalkan'
            ])->default('Menunggu Pembayaran');
            $table->string('recipient_name');
            $table->string('recipient_phone', 30);
            $table->text('shipping_address');
            $table->text('customer_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

