<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use App\Models\PoPeriod;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\OrderStatusHistory;
use App\Models\CancellationRequest;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic data matching proposal facts.
     */
    public function run(): void
    {
        // ==========================================
        // 1. USERS & CUSTOMERS (1 Owner, 2 Admin, Customers)
        // ==========================================
        $owner = User::create([
            'name' => 'Muhammad Anshari Hakam (Owner)',
            'email' => 'owner@uyuy.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OWNER,
        ]);

        $admin1 = User::create([
            'name' => 'Siti Nurhaliza (Admin 1)',
            'email' => 'admin1@uyuy.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $admin2 = User::create([
            'name' => 'Firdaus (Admin 2)',
            'email' => 'admin2@uyuy.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        // Primary Demo Customer
        $userCustomer1 = User::create([
            'name' => 'Raka Rahmatullah',
            'email' => 'customer@uyuy.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $customer1 = Customer::create([
            'user_id' => $userCustomer1->id,
            'full_name' => 'Raka Rahmatullah',
            'phone_number' => '081298765432',
            'default_address' => 'Jl. Telkom Pendidikan No. 12, RT 04 / RW 02, Cilandak Barat',
            'city' => 'Jakarta Selatan',
            'postal_code' => '12430',
        ]);

        // Secondary Customer
        $userCustomer2 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@uyuy.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $customer2 = Customer::create([
            'user_id' => $userCustomer2->id,
            'full_name' => 'Budi Santoso',
            'phone_number' => '081345678901',
            'default_address' => 'Jl. Kemang Raya No. 45, Bangka',
            'city' => 'Jakarta Selatan',
            'postal_code' => '12730',
        ]);

        // Third Customer
        $userCustomer3 = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@uyuy.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $customer3 = Customer::create([
            'user_id' => $userCustomer3->id,
            'full_name' => 'Dewi Lestari',
            'phone_number' => '081987654321',
            'default_address' => 'Jl. Dago Asri No. 18',
            'city' => 'Bandung',
            'postal_code' => '40135',
        ]);

        // Fourth Customer
        $userCustomer4 = User::create([
            'name' => 'Farhan Arhaj',
            'email' => 'farhan@uyuy.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $customer4 = Customer::create([
            'user_id' => $userCustomer4->id,
            'full_name' => 'Farhan Arhaj',
            'phone_number' => '081765432109',
            'default_address' => 'Jl. Kebon Jeruk Indah No. 8',
            'city' => 'Jakarta Barat',
            'postal_code' => '11530',
        ]);


        // ==========================================
        // 2. PO PERIODS (1 Active Open, 2 Closed)
        // ==========================================
        $poPeriod8 = PoPeriod::create([
            'name' => 'PO Batch 8 - Pertengahan Oktober',
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-20',
            'status' => PoPeriod::STATUS_CLOSED,
            'notes' => 'Siklus kedua Oktober. Pengiriman parfum ke pemesan telah tuntas.',
            'created_by' => $owner->id,
        ]);

        $poPeriod9 = PoPeriod::create([
            'name' => 'PO Batch 9 - Akhir Oktober',
            'start_date' => '2026-10-21',
            'end_date' => '2026-10-27',
            'status' => PoPeriod::STATUS_CLOSED,
            'notes' => 'Siklus akhir Oktober. Distribusi telah selesai.',
            'created_by' => $owner->id,
        ]);

        $poPeriod10 = PoPeriod::create([
            'name' => 'PO Batch 10 - Pekan Reguler',
            'start_date' => '2026-10-28',
            'end_date' => '2026-11-04',
            'status' => PoPeriod::STATUS_OPEN,
            'notes' => 'Siklus awal November. Kuota terbuka untuk pemesanan seluruh katalog In Parfume.',
            'created_by' => $owner->id,
        ]);


        // ==========================================
        // 3. PRODUCTS (In Parfume Perfume Catalog)
        // ==========================================
        $p1 = Product::create([
            'name' => 'Baccarat Rouge 540 Inspired',
            'brand' => 'In Parfume',
            'category' => 'Amber Floral',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Aroma mewah elegan perpaduan saffron, jasmine sambac, amberwood, dan cedarwood dengan ketahanan istimewa 8-12 jam.',
            'image_path' => 'products/perfume_1.jpg',
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'name' => 'Black Opium Inspired',
            'brand' => 'In Parfume',
            'category' => 'Sweet Gourmand',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Wewangian adiktif beraroma kopi hitam intens, vanilla manis lembut, dan bunga putih elegan nan sensual.',
            'image_path' => 'products/perfume_2.jpg',
            'is_active' => true,
        ]);

        $p3 = Product::create([
            'name' => 'Sauvage Inspired',
            'brand' => 'In Parfume',
            'category' => 'Fresh Woody',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Aroma maskulin segar yang tajam dari bergamot Calabria dan sentuhan amber kayu ambroxan yang memikat.',
            'image_path' => 'products/perfume_3.jpg',
            'is_active' => true,
        ]);

        $p4 = Product::create([
            'name' => 'English Pear & Freesia Inspired',
            'brand' => 'In Parfume',
            'category' => 'Fruity Floral',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Kesegaran buah pir King William yang matang berpadu anggun dengan aroma bunga freesia putih dan kayu amber.',
            'image_path' => 'products/perfume_4.jpg',
            'is_active' => true,
        ]);

        $p5 = Product::create([
            'name' => 'YSL Libre Inspired',
            'brand' => 'In Parfume',
            'category' => 'Floral Lavender',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Kebebasan sensual dengan perpaduan lavender Prancis, orange blossom Maroko, dan vanilla madagaskar.',
            'image_path' => 'products/perfume_5.jpg',
            'is_active' => true,
        ]);

        $p6 = Product::create([
            'name' => 'Acqua Di Gio Inspired',
            'brand' => 'In Parfume',
            'category' => 'Aquatic Fresh',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Aroma angin laut segar Mediterania dengan citrus alami dan kehangatan kayu cedarwood maskulin.',
            'image_path' => 'products/perfume_6.jpg',
            'is_active' => true,
        ]);

        $p7 = Product::create([
            'name' => 'Vanilla Lace Inspired',
            'brand' => 'In Parfume',
            'category' => 'Sweet Vanilla',
            'size_ml' => 60,
            'price' => 85000,
            'description' => 'Aroma vanilla hangat sensual dan musk yang menenangkan, feminin, dan manis menggoda sepanjang hari.',
            'image_path' => 'products/perfume_7.jpg',
            'is_active' => true,
        ]);

        $p8 = Product::create([
            'name' => 'Bleu de Chanel Inspired',
            'brand' => 'In Parfume',
            'category' => 'Woody Aromatic',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Eksplorasi wewangian maskulin elegan dari grapefruit, dupa, dan sandalwood yang kaya dan tahan lama.',
            'image_path' => 'products/perfume_8.jpg',
            'is_active' => true,
        ]);


        // ==========================================
        // 4. ORDERS & DETAILS & STATUS HISTORIES & PAYMENTS
        // ==========================================

        // Order 1: Batch 10 - Menunggu Pembayaran (Belum upload bukti)
        $order1 = Order::create([
            'order_number' => 'UJ-PO10-20261029-001',
            'customer_id' => $customer1->id,
            'po_period_id' => $poPeriod10->id,
            'total_amount' => 190000,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'recipient_name' => $customer1->full_name,
            'recipient_phone' => $customer1->phone_number,
            'shipping_address' => $customer1->default_address . ', ' . $customer1->city,
            'customer_notes' => 'Tolong bubble wrap tebal ya min.',
            'created_at' => '2026-10-29 14:20:00',
        ]);

        OrderDetail::create([
            'order_id' => $order1->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'size_ml' => $p1->size_ml,
            'unit_price' => $p1->price,
            'quantity' => 1,
            'subtotal' => 95000,
        ]);

        OrderDetail::create([
            'order_id' => $order1->id,
            'product_id' => $p2->id,
            'product_name' => $p2->name,
            'size_ml' => $p2->size_ml,
            'unit_price' => $p2->price,
            'quantity' => 1,
            'subtotal' => 95000,
        ]);

        Payment::create([
            'order_id' => $order1->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 190000,
            'proof_file_path' => null,
            'uploaded_at' => null,
            'verification_status' => Payment::STATUS_PENDING,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order1->id,
            'previous_status' => null,
            'new_status' => Order::STATUS_WAITING_PAYMENT,
            'remarks' => 'Pesanan berhasil dibuat oleh pelanggan pada PO Batch 10.',
            'changed_by' => $userCustomer1->id,
            'created_at' => '2026-10-29 14:20:00',
        ]);


        // Order 2: Batch 10 - Menunggu Pembayaran (Sudah upload bukti, Menunggu Verifikasi Admin)
        $order2 = Order::create([
            'order_number' => 'UJ-PO10-20261029-002',
            'customer_id' => $customer2->id,
            'po_period_id' => $poPeriod10->id,
            'total_amount' => 95000,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'recipient_name' => $customer2->full_name,
            'recipient_phone' => $customer2->phone_number,
            'shipping_address' => $customer2->default_address . ', ' . $customer2->city,
            'created_at' => '2026-10-29 15:05:00',
        ]);

        OrderDetail::create([
            'order_id' => $order2->id,
            'product_id' => $p3->id,
            'product_name' => $p3->name,
            'size_ml' => $p3->size_ml,
            'unit_price' => $p3->price,
            'quantity' => 1,
            'subtotal' => 95000,
        ]);

        Payment::create([
            'order_id' => $order2->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 95000,
            'proof_file_path' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=700&auto=format&fit=crop&q=80',
            'uploaded_at' => '2026-10-29 15:10:00',
            'verification_status' => Payment::STATUS_PENDING,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order2->id,
            'previous_status' => null,
            'new_status' => Order::STATUS_WAITING_PAYMENT,
            'remarks' => 'Pesanan dibuat.',
            'changed_by' => $userCustomer2->id,
            'created_at' => '2026-10-29 15:05:00',
        ]);


        // Order 3: Batch 10 - Pembayaran Diverifikasi (Valid oleh Admin 1)
        $order3 = Order::create([
            'order_number' => 'UJ-PO10-20261028-005',
            'customer_id' => $customer3->id,
            'po_period_id' => $poPeriod10->id,
            'total_amount' => 285000,
            'status' => Order::STATUS_PAYMENT_VERIFIED,
            'recipient_name' => $customer3->full_name,
            'recipient_phone' => $customer3->phone_number,
            'shipping_address' => $customer3->default_address . ', ' . $customer3->city,
            'created_at' => '2026-10-28 11:30:00',
        ]);

        OrderDetail::create([
            'order_id' => $order3->id,
            'product_id' => $p4->id,
            'product_name' => $p4->name,
            'size_ml' => $p4->size_ml,
            'unit_price' => $p4->price,
            'quantity' => 3,
            'subtotal' => 285000,
        ]);

        Payment::create([
            'order_id' => $order3->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 285000,
            'proof_file_path' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=700&auto=format&fit=crop&q=80',
            'uploaded_at' => '2026-10-28 11:35:00',
            'verification_status' => Payment::STATUS_VALID,
            'admin_notes' => 'Mutasi BCA masuk Rp 285.000 a.n. Dewi Lestari. Valid.',
            'verified_by' => $admin1->id,
            'verified_at' => '2026-10-28 12:00:00',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order3->id,
            'previous_status' => null,
            'new_status' => Order::STATUS_WAITING_PAYMENT,
            'remarks' => 'Pesanan dibuat.',
            'changed_by' => $userCustomer3->id,
            'created_at' => '2026-10-28 11:30:00',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order3->id,
            'previous_status' => Order::STATUS_WAITING_PAYMENT,
            'new_status' => Order::STATUS_PAYMENT_VERIFIED,
            'remarks' => 'Bukti pembayaran transfer BCA berhasil diverifikasi manual oleh admin.',
            'changed_by' => $admin1->id,
            'created_at' => '2026-10-28 12:00:00',
        ]);


        // Order 4: Batch 10 - Diproses (Sudah diverifikasi, masuk antrean belanja vendor)
        $order4 = Order::create([
            'order_number' => 'UJ-PO10-20261028-004',
            'customer_id' => $customer4->id,
            'po_period_id' => $poPeriod10->id,
            'total_amount' => 95000,
            'status' => Order::STATUS_PROCESSING,
            'recipient_name' => $customer4->full_name,
            'recipient_phone' => $customer4->phone_number,
            'shipping_address' => $customer4->default_address . ', ' . $customer4->city,
            'created_at' => '2026-10-28 10:15:00',
        ]);

        OrderDetail::create([
            'order_id' => $order4->id,
            'product_id' => $p5->id,
            'product_name' => $p5->name,
            'size_ml' => $p5->size_ml,
            'unit_price' => $p5->price,
            'quantity' => 1,
            'subtotal' => 95000,
        ]);

        Payment::create([
            'order_id' => $order4->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 95000,
            'proof_file_path' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=700&auto=format&fit=crop&q=80',
            'uploaded_at' => '2026-10-28 10:20:00',
            'verification_status' => Payment::STATUS_VALID,
            'admin_notes' => 'Lunas.',
            'verified_by' => $admin1->id,
            'verified_at' => '2026-10-28 10:30:00',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order4->id,
            'previous_status' => Order::STATUS_PAYMENT_VERIFIED,
            'new_status' => Order::STATUS_PROCESSING,
            'remarks' => 'Pesanan parfum dimasukkan ke dalam daftar belanja In Parfume Bandung.',
            'changed_by' => $admin2->id,
            'created_at' => '2026-10-28 11:00:00',
        ]);


        // Order 5: Batch 9 - Barang Dikirim (Milik Customer 1 Raka)
        $order5 = Order::create([
            'order_number' => 'UJ-PO09-20261021-042',
            'customer_id' => $customer1->id,
            'po_period_id' => $poPeriod9->id,
            'total_amount' => 95000,
            'status' => Order::STATUS_SHIPPED,
            'recipient_name' => $customer1->full_name,
            'recipient_phone' => $customer1->phone_number,
            'shipping_address' => $customer1->default_address . ', ' . $customer1->city,
            'created_at' => '2026-10-21 10:15:00',
        ]);

        OrderDetail::create([
            'order_id' => $order5->id,
            'product_id' => $p3->id,
            'product_name' => $p3->name,
            'size_ml' => $p3->size_ml,
            'unit_price' => $p3->price,
            'quantity' => 1,
            'subtotal' => 95000,
        ]);

        Payment::create([
            'order_id' => $order5->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 95000,
            'proof_file_path' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=700&auto=format&fit=crop&q=80',
            'uploaded_at' => '2026-10-21 10:25:00',
            'verification_status' => Payment::STATUS_VALID,
            'admin_notes' => 'Lunas BCA.',
            'verified_by' => $admin1->id,
            'verified_at' => '2026-10-21 11:00:00',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order5->id,
            'previous_status' => Order::STATUS_PROCESSING,
            'new_status' => Order::STATUS_SHIPPED,
            'remarks' => 'Paket parfum telah dikirim via ekspedisi JNE (No. Resi: JNE872910291).',
            'changed_by' => $admin1->id,
            'created_at' => '2026-10-25 14:00:00',
        ]);


        // Order 6: Batch 8 - Selesai (Milik Customer 1 Raka)
        $order6 = Order::create([
            'order_number' => 'UJ-PO08-20261014-019',
            'customer_id' => $customer1->id,
            'po_period_id' => $poPeriod8->id,
            'total_amount' => 285000,
            'status' => Order::STATUS_COMPLETED,
            'recipient_name' => $customer1->full_name,
            'recipient_phone' => $customer1->phone_number,
            'shipping_address' => $customer1->default_address . ', ' . $customer1->city,
            'created_at' => '2026-10-14 16:30:00',
        ]);

        OrderDetail::create([
            'order_id' => $order6->id,
            'product_id' => $p6->id,
            'product_name' => $p6->name,
            'size_ml' => $p6->size_ml,
            'unit_price' => $p6->price,
            'quantity' => 3,
            'subtotal' => 285000,
        ]);

        Payment::create([
            'order_id' => $order6->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 285000,
            'proof_file_path' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=700&auto=format&fit=crop&q=80',
            'uploaded_at' => '2026-10-14 16:40:00',
            'verification_status' => Payment::STATUS_VALID,
            'admin_notes' => 'Lunas.',
            'verified_by' => $admin2->id,
            'verified_at' => '2026-10-14 17:00:00',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order6->id,
            'previous_status' => Order::STATUS_SHIPPED,
            'new_status' => Order::STATUS_COMPLETED,
            'remarks' => 'Paket telah diterima dengan baik oleh penerima. Transaksi PO selesai.',
            'changed_by' => $admin2->id,
            'created_at' => '2026-10-18 13:00:00',
        ]);


        // Order 7: Batch 10 - Ada Pengajuan Pembatalan (Menunggu Persetujuan Admin)
        $order7 = Order::create([
            'order_number' => 'UJ-PO10-20261029-003',
            'customer_id' => $customer4->id,
            'po_period_id' => $poPeriod10->id,
            'total_amount' => 95000,
            'status' => Order::STATUS_WAITING_PAYMENT,
            'recipient_name' => $customer4->full_name,
            'recipient_phone' => $customer4->phone_number,
            'shipping_address' => $customer4->default_address . ', ' . $customer4->city,
            'created_at' => '2026-10-29 15:30:00',
        ]);

        OrderDetail::create([
            'order_id' => $order7->id,
            'product_id' => $p7->id,
            'product_name' => $p7->name,
            'size_ml' => $p7->size_ml,
            'unit_price' => $p7->price,
            'quantity' => 1,
            'subtotal' => 95000,
        ]);

        CancellationRequest::create([
            'order_id' => $order7->id,
            'reason' => 'Salah memilih varian parfum saat checkout, ingin memesan Baccarat Rouge 540.',
            'status' => CancellationRequest::STATUS_PENDING,
            'created_at' => '2026-10-29 16:00:00',
        ]);


        // Order 8: Batch 9 - Pembatalan Disetujui (Status Order: Dibatalkan)
        $order8 = Order::create([
            'order_number' => 'UJ-PO09-20261020-008',
            'customer_id' => $customer3->id,
            'po_period_id' => $poPeriod9->id,
            'total_amount' => 95000,
            'status' => Order::STATUS_CANCELLED,
            'recipient_name' => $customer3->full_name,
            'recipient_phone' => $customer3->phone_number,
            'shipping_address' => $customer3->default_address . ', ' . $customer3->city,
            'created_at' => '2026-10-20 09:00:00',
        ]);

        OrderDetail::create([
            'order_id' => $order8->id,
            'product_id' => $p8->id,
            'product_name' => $p8->name,
            'size_ml' => $p8->size_ml,
            'unit_price' => $p8->price,
            'quantity' => 1,
            'subtotal' => 95000,
        ]);

        CancellationRequest::create([
            'order_id' => $order8->id,
            'reason' => 'Salah mengisi alamat tujuan pengiriman luar kota.',
            'status' => CancellationRequest::STATUS_APPROVED,
            'admin_notes' => 'Pembatalan disetujui. Silakan lakukan pemesanan ulang dengan alamat baru.',
            'reviewed_by' => $admin1->id,
            'reviewed_at' => '2026-10-20 10:00:00',
            'created_at' => '2026-10-20 09:30:00',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order8->id,
            'previous_status' => Order::STATUS_WAITING_PAYMENT,
            'new_status' => Order::STATUS_CANCELLED,
            'remarks' => 'Pesanan dibatalkan atas persetujuan admin Siti Nurhaliza.',
            'changed_by' => $admin1->id,
            'created_at' => '2026-10-20 10:00:00',
        ]);
    }
}
