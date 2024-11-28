<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGrade extends Model
{
    use HasFactory;
    protected $table = 'user_grades';
    public $timestamps = false;

    protected $fillable = [
        'created_by',
        'updated_by',
    ];


}
