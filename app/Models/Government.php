<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Government extends Model
{
    use HasFactory;
    protected $table = 'governments';
    public $timestamps = false;

    protected $fillable = [
        'name'
    ];
    public function region()
    {
        return $this->hasMany(outgoings::class);

    }
    public function points()
    {
        return $this->hasMany(Point::class);
    }
    public function groupPoints()
    {
        return $this->belongsTo(Grouppoint::class);
    }

    protected $appends = ['hash_id'];

    public function getHashIdAttribute()
    {
        return md5($this->id);
        //return Crypt::encryptString($this->id);
    }
}
