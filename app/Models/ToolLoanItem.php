<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolLoanItem extends Model
{
    protected $fillable = [
        'tool_loan_id',
        'tool_name',
        'tool_detail',
        'quantity_loaned',
        'quantity_returned',
        'quantity_repair',
        'quantity_repaired',
        'quantity_lost',
        'last_return_condition',
    ];

    protected $casts = [
        'quantity_loaned' => 'integer',
        'quantity_returned' => 'integer',
        'quantity_repair' => 'integer',
        'quantity_repaired' => 'integer',
        'quantity_lost' => 'integer',
    ];

    public function loan()
    {
        return $this->belongsTo(ToolLoan::class, 'tool_loan_id');
    }

    public function pendingQuantity(): int
    {
        return max(0, $this->quantity_loaned - $this->quantity_returned - $this->quantity_repair - $this->quantity_lost);
    }

    public function pendingRepairQuantity(): int
    {
        return max(0, $this->quantity_repair - $this->quantity_repaired);
    }
}
