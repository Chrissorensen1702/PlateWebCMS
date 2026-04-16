<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSolution extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'package_key',
        'locations',
        'staff',
        'bookings',
        'sections',
        'package_options',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'locations' => 'integer',
            'staff' => 'integer',
            'bookings' => 'integer',
            'sections' => 'integer',
            'package_options' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
