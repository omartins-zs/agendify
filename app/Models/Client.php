<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'notes',
        'last_visit_at',
    ];

    protected function casts(): array
    {
        return [
            'last_visit_at' => 'datetime',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }
}
