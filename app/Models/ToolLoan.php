<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolLoan extends Model
{
    protected $fillable = [
        'loaned_by',
        'employee_name',
        'employee_code',
        'employee_area',
        'taken_at',
        'expected_return_at',
        'status',
        'evidence_out',
        'notes',
        'returned_at',
        'evidence_return',
        'return_notes',
        'repair_notes',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'expected_return_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function loanedBy()
    {
        return $this->belongsTo(User::class, 'loaned_by');
    }

    public function items()
    {
        return $this->hasMany(ToolLoanItem::class);
    }

    public function pendingItemsCount(): int
    {
        return $this->items->filter(fn (ToolLoanItem $item): bool => $item->pendingQuantity() > 0)->count();
    }
}
