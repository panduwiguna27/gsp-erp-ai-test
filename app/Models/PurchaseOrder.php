<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'supplier_id', 'status', 'order_date', 'total', 'notes', 'user_id'])]
class PurchaseOrder extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'received', 'cancelled'];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public static function nextCode(): string
    {
        return 'PO-'.str_pad((string) ((static::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }

    public function recalculateTotal(): float
    {
        $total = (float) $this->items()->sum('subtotal');
        $this->total = $total;
        $this->save();

        return $total;
    }

    public function isReceivable(): bool
    {
        return $this->status !== 'received';
    }
}
