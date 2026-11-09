<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'customer_id', 'status', 'order_date', 'total', 'notes', 'user_id'])]
class SalesOrder extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'confirmed', 'shipped', 'cancelled'];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public static function nextCode(): string
    {
        return 'SO-'.str_pad((string) ((static::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }

    public function recalculateTotal(): float
    {
        $total = (float) $this->items()->sum('subtotal');
        $this->total = $total;
        $this->save();

        return $total;
    }

    public function isShippable(): bool
    {
        return ! in_array($this->status, ['shipped', 'cancelled'], true);
    }
}
