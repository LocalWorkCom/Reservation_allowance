<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'amount', 'date', 'day', 'sector_id', 'departement_id', 'grade_id', 'created_by'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function departements()
    {
        return $this->belongsTo(departements::class, 'departement_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the relationship with the Grade model if needed
    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function sector()
{
    return $this->belongsTo(Sector::class, 'sector_id');
}
public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
}
