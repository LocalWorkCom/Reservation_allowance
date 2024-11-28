<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class outgoings extends Model
{
    use HasFactory;
    protected $table = 'outgoings';
    protected $fillable = [
        "name",
        "num",
        "note",
        "person_to",
        "active",
        "created_by",
        "updated_by ",
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function departments()
    {
        return $this->belongsTo(departements::class);
    }

}
