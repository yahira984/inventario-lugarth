<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisualSearchFeedback extends Model
{
    protected $table = 'visual_search_feedback';

    protected $fillable = [
        'user_id',
        'suggested_material_id',
        'selected_material_id',
        'query_signature',
        'was_correct',
        'confidence',
        'context',
    ];

    protected $casts = [
        'was_correct' => 'boolean',
        'confidence' => 'decimal:3',
        'context' => 'array',
    ];
}
