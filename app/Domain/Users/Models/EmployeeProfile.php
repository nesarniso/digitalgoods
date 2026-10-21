<?php

namespace App\Domain\Users\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'department',
        'designation',
        'area',
        'reports_to_user_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reports_to_user_id');
    }
}
