<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;


class departements extends Model
{
    use HasFactory , SoftDeletes, Uuid;

    protected $fillable = [
        'name',
        'manger',
        'manger_assistance',
        'description',
        'parent_id'
    ];

    protected $appends = ['hash_id'];

    protected $hidden = [
        'id'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function employees()
    {
        return $this->hasMany(User::class , 'department_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manger');
    }

    public function managerAssistant()
    {
        return $this->belongsTo(User::class, 'manger_assistance');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');

    }

    public function iotelegrams()
    {
        return $this->hasMany(Iotelegram::class , 'from_departement');
    }

    public function outgoings()
    {
        return $this->hasMany(outgoings::class, 'created_department');
    }

    public function parent()
    {
        return $this->belongsTo(departements::class, 'parent_id');
    }

    public function sectors()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }

    public function reservationAllowances()
    {
        return $this->hasMany(ReservationAllowance::class, 'departement_id');
    }

    // Define the relationship to the child departments
    public function children()
    {
        return $this->hasMany(departements::class, 'parent_id');
    }
    public function childrenDepartments()
    {
        return $this->hasMany(departements::class, 'parent_id')->with('children');

    }
    public function violations()
    {
        return $this->hasMany(ViolationTypes::class);
    }
    public function getAllChildren()
    {
        $children = collect([]);

        foreach ($this->children as $child) {
            $children->push($child);
            $children = $children->merge($child->getAllChildren());
        }

        return $children;
    }

    public function getHashIdAttribute()
    {
        return md5($this->id);
    }
}
