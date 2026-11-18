<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('size_ml');
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'size_ml']);
        });

        DB::table('products')->orderBy('id')->chunkById(200, function ($products): void {
            $now = now();
            $variants = $products->map(fn ($product) => [
                'product_id' => $product->id,
                'size_ml' => $product->size_ml,
                'price' => $product->price,
                'cost_price' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if ($variants !== []) {
                DB::table('product_variants')->insertOrIgnore($variants);
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('payment_deadline_at')->nullable()->after('total_amount');
            $table->string('shipping_postal_code', 10)->nullable()->after('shipping_address');
            $table->string('courier', 50)->nullable()->after('shipping_postal_code');
            $table->string('tracking_number', 100)->nullable()->after('courier');
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('tracking_number');
            $table->index(['status', 'payment_deadline_at'], 'orders_expiry_lookup_idx');
        });

        DB::table('orders')
            ->where('status', 'Menunggu Pembayaran')
            ->whereNull('payment_deadline_at')
            ->orderBy('id')
            ->chunkById(200, function ($orders): void {
                foreach ($orders as $order) {
                    if ($order->created_at) {
                        DB::table('orders')->where('id', $order->id)->update([
                            'payment_deadline_at' => Carbon::parse($order->created_at)->addDay(),
                        ]);
                    }
                }
            });

        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->nullable()->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_expiry_lookup_idx');
            $table->dropColumn([
                'payment_deadline_at',
                'shipping_postal_code',
                'courier',
                'tracking_number',
                'shipping_cost',
            ]);
        });

        Schema::dropIfExists('product_variants');
    }
};
