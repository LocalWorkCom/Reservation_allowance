<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceType extends Model
{
    use HasFactory;
    protected $appends = ['hash_id'];

    public function absences()
    {
        return $this->hasMany(Absence::class, 'absence_types_id');
    }
    public function getHashIdAttribute()
    {
        return md5($this->id);
    }
}
