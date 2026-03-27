<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityRule extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'weekday',
        'start_time',
        'end_time',
        'slot_interval_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'slot_interval_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
