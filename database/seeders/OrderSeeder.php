<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::orderBy('id')->get();
        $supplierIds = Supplier::orderBy('id')->pluck('id')->all();
        $customerIds = Customer::orderBy('id')->pluck('id')->all();
        $userIds = User::orderBy('id')->pluck('id')->all();

        if ($products->count() < 4 || empty($supplierIds) || empty($customerIds)) {
            return;
        }

        // ---- Purchase orders: 12 documents, the first 9 are received into stock.
        foreach (range(0, 11) as $i) {
            $receive = $i < 9;

            $order = DB::transaction(function () use ($i, $products, $supplierIds, $userIds, $receive) {
                $order = PurchaseOrder::create([
                    'code' => 'PO-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                    'supplier_id' => $supplierIds[$i % count($supplierIds)],
                    'status' => 'draft',
                    'order_date' => now()->subDays(30 - $i)->toDateString(),
                    'notes' => $receive ? 'Goods accepted at warehouse.' : 'Awaiting supplier delivery.',
                    'user_id' => $userIds[$i % count($userIds)],
                ]);

                foreach ($this->lines($products, $i) as $line) {
                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'qty' => $line['qty'],
                        'unit_cost' => $line['product']->cost_price,
                        'subtotal' => $line['qty'] * (float) $line['product']->cost_price,
                    ]);
                }

                $order->recalculateTotal();

                if ($receive) {
                    $order->items->each(fn ($item) => $item->product->adjustStock(
                        (int) $item->qty, 'in', $order->code, 'Purchase received', $order->user_id
                    ));
                    $order->update(['status' => 'received']);
                } elseif ($i === 11) {
                    $order->update(['status' => 'cancelled']);
                }

                return $order;
            });
        }

        // ---- Sales orders: 12 documents; shipped only when stock allows.
        foreach (range(0, 11) as $i) {
            DB::transaction(function () use ($i, $products, $customerIds, $userIds) {
                $order = SalesOrder::create([
                    'code' => 'SO-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                    'customer_id' => $customerIds[$i % count($customerIds)],
                    'status' => 'draft',
                    'order_date' => now()->subDays(24 - ($i * 2))->toDateString(),
                    'notes' => null,
                    'user_id' => $userIds[$i % count($userIds)],
                ]);

                $lines = $this->lines($products, $i + 3);

                foreach ($lines as $line) {
                    $order->items()->create([
                        'product_id' => $line['product']->id,
                        'qty' => max(1, intdiv($line['qty'], 3)),
                        'unit_price' => $line['product']->sale_price,
                        'subtotal' => max(1, intdiv($line['qty'], 3)) * (float) $line['product']->sale_price,
                    ]);
                }

                $order->recalculateTotal();

                if ($i === 11) {
                    $order->update(['status' => 'cancelled']);

                    return;
                }

                $order->load('items.product');
                $canShip = $order->items->every(
                    fn ($item) => $item->product && $item->product->stock_qty >= $item->qty
                );

                if ($canShip && $i < 8) {
                    $order->items->each(fn ($item) => $item->product->adjustStock(
                        -(int) $item->qty, 'out', $order->code, 'Order shipped', $order->user_id
                    ));
                    $order->update(['status' => 'shipped']);
                } elseif ($i % 2 === 0) {
                    $order->update(['status' => 'confirmed']);
                }
            });
        }
    }

    /**
     * Two or three deterministic product lines per document.
     */
    private function lines($products, int $seed): array
    {
        $count = 2 + ($seed % 2);
        $lines = [];

        for ($j = 0; $j < $count; $j++) {
            $product = $products[($seed * 3 + $j * 5) % $products->count()];
            $lines[] = ['product' => $product, 'qty' => 5 + (($seed + $j) % 14)];
        }

        return collect($lines)->unique(fn ($line) => $line['product']->id)->values()->all();
    }
}
