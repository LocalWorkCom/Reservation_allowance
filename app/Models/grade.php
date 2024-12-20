<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class grade extends Model
{
    use HasFactory;
    protected $table = 'grades';
    // public $timestamps = false;

    protected $fillable = ['name', 'type', 'value_all', 'value_part'];

    public function grades()
    {
        return $this->hasMany(User::class , 'id');
    }
}
