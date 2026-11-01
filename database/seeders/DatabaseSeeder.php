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
        // 3. PRODUCTS (In Parfume Fragrance 15 Official Best Sellers)
        // ==========================================
        $p1 = Product::create([
            'name' => 'Baccarat Rouge 540',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Amber Floral',
            'size_ml' => 60,
            'price' => 120000,
            'description' => 'Varian terlaris global dari In Parfume Fragrance Bandung. Perpaduan mewah antara saffron, bitter almond, jasmine sambac, amberwood, dan cedarwood dengan ketahanan istimewa 10-14 jam. Karakter woody-floral-amber yang memikat dan elegan (Unisex).',
            'image_path' => 'products/perfume_1.jpg',
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'name' => 'Christian Dior Sauvage EDP',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Fresh Woody',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Best seller parfum pria terpopuler di In Parfume. Kesegaran tajam dari bergamot Calabria berpadu rempah lada Sichuan hangat, lavender, ambroxan kayu, dan vanila Papua Nugini yang maskulin dan sensual (Pria). Ketahanan 8-12 jam.',
            'image_path' => 'products/perfume_2.jpg',
            'is_active' => true,
        ]);

        $p3 = Product::create([
            'name' => 'Creed Aventus',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Fruity Chypre',
            'size_ml' => 60,
            'price' => 120000,
            'description' => 'Ikon maskulinitas modern dan kesuksesan. Kombinasi kontras nan legendaris antara kesegaran nanas dan apel hijau dengan asap kayu birch (smoky), patchouli, dan ambergris yang memancarkan karisma dominan (Pria). Ketahanan 10-14 jam.',
            'image_path' => 'products/perfume_3.jpg',
            'is_active' => true,
        ]);

        $p4 = Product::create([
            'name' => "YSL Libre L'Absolu Platine",
            'brand' => 'In Parfume Fragrance',
            'category' => 'Floral Lavender',
            'size_ml' => 60,
            'price' => 120000,
            'description' => 'Best seller kemewahan modern dengan karakter floral lavender. Menghadirkan perpaduan unik lavender Prancis putih (white lavender) dengan sensasi metallic icy yang dingin, bersih, dan elegan berpadu vanilla Bourbon (Wanita). Ketahanan 10-12 jam.',
            'image_path' => 'products/perfume_4.jpg',
            'is_active' => true,
        ]);

        $p5 = Product::create([
            'name' => 'Chanel No. 5',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Floral Aldehyde',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Parfum klasik legendaris sepanjang masa di In Parfume Bandung. Perpaduan molekul aldehydes yang berkilau bersih, buket bunga ylang-ylang, melati, mawar Mei, serta kehangatan sandalwood dan vanilla yang sangat anggun dan abadi (Wanita). Ketahanan 8-12 jam.',
            'image_path' => 'products/perfume_5.jpg',
            'is_active' => true,
        ]);

        $p6 = Product::create([
            'name' => 'Bvlgari Aqua Pour Homme',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Aromatic Aquatic',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Standar emas parfum aquatic pria. Menghadirkan kesegaran samudra biru gelap dengan aroma mandarin orange, petitgrain, rumput laut Posidonia Oceanica alami, dan mineral cedarwood yang maskulin dan menyegarkan (Pria). Ketahanan 7-10 jam.',
            'image_path' => 'products/perfume_6.jpg',
            'is_active' => true,
        ]);

        $p7 = Product::create([
            'name' => 'Carolina Herrera 212 Sexy Men',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Amber Spicy',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Wewangian pria paling menggoda dan magnetis. Perpaduan awal citrus bergamot segar yang bertransformasi lembut menjadi kehangatan kapulaga, lada hitam, vanilla manis lekat, dan kayu guaiac yang sensual (Pria). Ketahanan 8-12 jam.',
            'image_path' => 'products/perfume_7.jpg',
            'is_active' => true,
        ]);

        $p8 = Product::create([
            'name' => 'Etienne Aigner In Leather Man',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Warm Spicy Leather',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Parfum leather terlaris di gerai In Parfume Bandung. Menampilkan aroma kulit mewah yang lembut dan bersih, berpadu buah apel merah, nanas segar, pala rempah, dan kayu cedarwood yang maskulin berkelas (Pria). Ketahanan 8-10 jam.',
            'image_path' => 'products/perfume_8.jpg',
            'is_active' => true,
        ]);

        $p9 = Product::create([
            'name' => 'The Body Shop White Musk',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Clean Floral Musk',
            'size_ml' => 60,
            'price' => 80000,
            'description' => 'Varian aroma harian paling dicari dan digemari. Menyajikan aroma bersih nan menenangkan seperti kulit segar sehabis mandi, perpaduan floral lily, ylang-ylang, dan clean synthetic musk yang nyaman dipakai sepanjang hari (Unisex). Ketahanan 6-9 jam.',
            'image_path' => 'products/perfume_9.jpg',
            'is_active' => true,
        ]);

        $p10 = Product::create([
            'name' => 'Parfums de Marly Delina',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Floral Rose',
            'size_ml' => 60,
            'price' => 120000,
            'description' => 'Mahakarya parfum niche feminin paling dicari. Menampilkan kemewahan mawar Turki (Turkish rose) yang dipadukan dengan kesegaran leci manis asam, rhubarb segar, peony, vanilla lembut, dan cashmeran hangat nan memukau (Wanita). Ketahanan 10-14 jam.',
            'image_path' => 'products/perfume_10.jpg',
            'is_active' => true,
        ]);

        $p11 = Product::create([
            'name' => 'Giorgio Armani Acqua Di Gio Absolu',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Woody Aquatic',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Pertemuan elegan antara kesegaran air laut dan kehangatan kayu. Komposisi marine notes, buah pir, grapefruit segar, rosemary, tonka bean manis, dan amberwood yang mewah dan maskulin (Pria). Ketahanan 8-12 jam.',
            'image_path' => 'products/perfume_11.webp',
            'is_active' => true,
        ]);

        $p12 = Product::create([
            'name' => 'Azzaro The Most Wanted',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Amber Spicy Gourmand',
            'size_ml' => 60,
            'price' => 95000,
            'description' => 'Parfum malam pria modern yang sangat adiktif dan trending di In Parfume. Dibuka dengan kapulaga merah yang hangat membara, toffee karamel manis pekat yang meleleh, dan bourbon vanilla amberwood yang sangat memikat (Pria). Ketahanan 9-13 jam.',
            'image_path' => 'products/perfume_12.jpg',
            'is_active' => true,
        ]);

        $p13 = Product::create([
            'name' => 'Maison Margiela By The Fireplace',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Woody Gourmand',
            'size_ml' => 60,
            'price' => 120000,
            'description' => 'Koleksi replica niche paling dicari untuk suasana hangat dan intim. Menggugah kenangan perapian musim dingin melalui perpaduan cengkeh rempah, chestnut panggang manis, guaiac wood berasap halus, dan vanilla hangat (Unisex). Ketahanan 9-12 jam.',
            'image_path' => 'products/perfume_13.png',
            'is_active' => true,
        ]);

        $p14 = Product::create([
            'name' => 'Diptyque Philosykos',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Aromatic Green Woody',
            'size_ml' => 60,
            'price' => 120000,
            'description' => 'Varian niche hijau alami terlaris yang didedikasikan untuk keindahan pohon ara (fig tree). Menghadirkan kesegaran daun ara yang dihancurkan, buah fig manis alami, santan kelapa creamy, dan kehangatan kayu cedarwood (Unisex). Ketahanan 7-10 jam.',
            'image_path' => 'products/perfume_14.jpg',
            'is_active' => true,
        ]);

        $p15 = Product::create([
            'name' => 'Parfums de Marly Layton',
            'brand' => 'In Parfume Fragrance',
            'category' => 'Oriental Floral Spicy',
            'size_ml' => 60,
            'price' => 120000,
            'description' => 'Top best seller kemewahan oriental niche. Menggabungkan kesegaran apel hijau renyah dan lavender aromatik dengan kehangatan rempah kapulaga, bunga geranium, melati, serta kelembutan vanilla Madagascar yang adiktif (Unisex). Ketahanan 10-14 jam.',
            'image_path' => 'products/perfume_15.jpg',
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
            'total_amount' => 215000,
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
            'subtotal' => 120000,
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
            'amount' => 215000,
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
            'total_amount' => 120000,
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
            'subtotal' => 120000,
        ]);

        Payment::create([
            'order_id' => $order2->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 120000,
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
            'total_amount' => 240000,
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
            'quantity' => 2,
            'subtotal' => 240000,
        ]);

        Payment::create([
            'order_id' => $order3->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 240000,
            'proof_file_path' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=700&auto=format&fit=crop&q=80',
            'uploaded_at' => '2026-10-28 11:35:00',
            'verification_status' => Payment::STATUS_VALID,
            'admin_notes' => 'Mutasi BCA masuk Rp 240.000 a.n. Dewi Lestari. Valid.',
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
            'total_amount' => 120000,
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
            'subtotal' => 120000,
        ]);

        Payment::create([
            'order_id' => $order5->id,
            'payment_method' => 'BCA Merchant',
            'amount' => 120000,
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
