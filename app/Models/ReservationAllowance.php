<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationAllowance extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function departements()
    {
        return $this->belongsTo(departements::class, 'departement_id');
    }
}
