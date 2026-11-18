<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateUserId = DB::table('customers')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->value('user_id');

        if ($duplicateUserId !== null) {
            throw new \RuntimeException("Cannot enforce one customer profile per user: duplicate customers.user_id {$duplicateUserId} exists. Resolve duplicate profiles, then rerun migrations.");
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('user_id', 'customers_user_id_unique');
        });

        Schema::table('po_periods', function (Blueprint $table) {
            $table->index('status', 'po_periods_status_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'created_at'], 'products_active_created_at_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at'], 'orders_customer_created_at_index');
            $table->index(['po_period_id', 'status'], 'orders_po_status_index');
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->index(['order_id', 'product_id'], 'order_details_order_product_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['verification_status', 'created_at'], 'payments_status_created_at_index');
            $table->index('uploaded_at', 'payments_uploaded_at_index');
        });

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->index(['order_id', 'created_at'], 'histories_order_created_at_index');
        });

        Schema::table('cancellation_requests', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'cancellations_status_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('cancellation_requests', function (Blueprint $table) {
            $table->dropIndex('cancellations_status_created_at_index');
        });

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->dropIndex('histories_order_created_at_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_created_at_index');
            $table->dropIndex('payments_uploaded_at_index');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('order_details_order_product_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_created_at_index');
            $table->dropIndex('orders_po_status_index');
            $table->dropIndex('orders_status_created_at_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_created_at_index');
        });

        Schema::table('po_periods', function (Blueprint $table) {
            $table->dropIndex('po_periods_status_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_user_id_unique');
        });
    }
};
