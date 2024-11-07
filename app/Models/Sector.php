<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Hashids\Hashids;

class Sector extends Model
{
    use HasFactory;
    protected $table = 'sectors';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'governments_IDs',
        'created_by',
        'updated_by',
    ];

    // protected $hidden = [
    //     'id'
    // ];

    protected $casts = [
        'governments_IDs' => 'array', // Automatically cast the attribute to an array
    ];

    protected $appends = ['hash_id'];

    public function government()
    {
        return $this->belongsTo(Government::class, 'governments_IDs', 'id');
    }
    public function points()
    {
        return $this->hasMany(Point::class);
    }

    public function departements()
    {
        return $this->hasMany(departements::class, 'sector_id');
    }
    public function manager_name()
    {
        return $this->belongsTo(User::class, 'manager');
    }

    public function getHashIdAttribute()
    {
        return md5($this->id);
    }
}
