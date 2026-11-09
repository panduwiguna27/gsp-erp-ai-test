<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sku', 'name', 'category', 'unit',
    'cost_price', 'sale_price', 'stock_qty', 'reorder_level', 'is_active',
])]
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock_qty' => 'integer',
            'reorder_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_qty', '<=', 'reorder_level');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(
            fn (Builder $s) => $s->where('sku', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%")
        ));
    }

    public function isLowStock(): bool
    {
        return $this->stock_qty <= $this->reorder_level;
    }

    public function stockValue(): float
    {
        return (float) $this->cost_price * (int) $this->stock_qty;
    }

    /**
     * Change stock and write an audit-trail movement in one step.
     */
    public function adjustStock(
        int $delta,
        string $type,
        ?string $reference = null,
        ?string $note = null,
        ?int $userId = null,
    ): StockMovement {
        $before = (int) $this->stock_qty;

        $this->stock_qty = $before + $delta;
        $this->save();

        return StockMovement::create([
            'product_id' => $this->id,
            'type' => $type,
            'qty' => $delta,
            'stock_before' => $before,
            'stock_after' => $this->stock_qty,
            'reference' => $reference,
            'note' => $note,
            'user_id' => $userId,
        ]);
    }
}
