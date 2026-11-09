<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            'PT Sumber Rejeki', 'CV Maju Bersama', 'PT Tani Subur', 'PT Wings Abadi',
            'CV Sinar Terang', 'PT Indofood Sukses', 'CV Berkah Jaya', 'PT Garuda Food',
            'CV Makmur Sentosa', 'PT Unggul Prima', 'CV Harapan Baru', 'PT Nusantara Ritel',
        ];
        foreach ($suppliers as $i => $name) {
            Supplier::updateOrCreate(['name' => $name], [
                'phone' => '021-555'.str_pad((string) (101 + $i), 3, '0', STR_PAD_LEFT),
                'email' => 'sales@'.strtolower(explode(' ', $name)[1]).'.co.id',
                'address' => 'Jl. Industri Raya No. '.($i + 1).', Jakarta',
                'is_active' => true,
            ]);
        }

        $customers = [
            'Toko Makmur', 'Warung Ibu Sri', 'CV Rasa Nusantara', 'Toko Serba Ada',
            'RM Sedap Malam', 'Minimart Berkah', 'Toko Wong Cilik', 'CV Mitra Sejahtera',
            'Warung Pak Darmo', 'Toko Lima Saudara', 'RM Padang Jaya', 'Grosir Murah Banget',
        ];
        foreach ($customers as $i => $name) {
            Customer::updateOrCreate(['name' => $name], [
                'phone' => '0812-1110-'.str_pad((string) (1 + $i), 4, '0', STR_PAD_LEFT),
                'email' => 'order'.$i.'@customer.id',
                'address' => 'Jl. Melati No. '.($i + 1).', Bandung',
                'is_active' => true,
            ]);
        }

        $catalogue = [
            ['SKU-001', 'Beras Pandan Wangi 5kg', 'Grocery', 'sack', 58000, 72000, 40, 10],
            ['SKU-002', 'Minyak Goreng Sania 2L', 'Grocery', 'bottle', 32000, 41000, 60, 15],
            ['SKU-003', 'Gula Pasir Premium 1kg', 'Grocery', 'pack', 14000, 19000, 30, 20],
            ['SKU-004', 'Mie Goreng Inferno (strip)', 'Snack', 'strip', 11000, 15000, 120, 30],
            ['SKU-005', 'Sabun Mandi Kupu-Kupu', 'Household', 'pcs', 3500, 5500, 80, 25],
            ['SKU-006', 'Kopi Kapal Api Sachet (box)', 'Beverage', 'box', 26000, 34000, 25, 10],
            ['SKU-007', 'Tepung Terigu Segitiga 1kg', 'Grocery', 'pack', 12500, 17000, 45, 15],
            ['SKU-008', 'Telur Ayam Negeri (kg)', 'Fresh', 'kg', 27000, 34000, 35, 12],
            ['SKU-009', 'Susu UHT Ultra Milk 250ml', 'Beverage', 'pcs', 6500, 9000, 96, 40],
            ['SKU-010', 'Detergent Daeng 400g', 'Household', 'pcs', 9800, 13500, 2, 20],
            ['SKU-011', 'Kecap Manis Bango 1L', 'Grocery', 'bottle', 24000, 31000, 18, 10],
            ['SKU-012', 'Aqua Galon 19L', 'Beverage', 'galon', 18000, 23000, 1, 8],
        ];

        $admin = User::where('username', 'admin')->first();

        foreach ($catalogue as [$sku, $name, $category, $unit, $cost, $price, $opening, $reorder]) {
            $product = Product::updateOrCreate(['sku' => $sku], [
                'name' => $name, 'category' => $category, 'unit' => $unit,
                'cost_price' => $cost, 'sale_price' => $price,
                'reorder_level' => $reorder, 'stock_qty' => 0, 'is_active' => true,
            ]);

            if ($product->movements()->count() === 0 && $opening > 0) {
                $product->adjustStock($opening, 'in', 'OPENING', 'Opening stock balance', $admin?->id);
            }
        }
    }
}
