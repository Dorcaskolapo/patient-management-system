<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'othernames',
        'lastname',
        'email',
        'dob',
        'marital_status',
        'gender',
        'phone_number',
        'bloodgroup',
        'genotype',
        'allergies',
        'religion',
        'address',
        'slug',
    ];

    public function prescriptions() {
        return $this->hasMany(Prescription::class);
    }
     
    public function tests() {
        return $this->hasMany(Test::class);
    }
     
    public function bloodgroup(){
        return $this->belongsTo(Bloodgroup::class);
    }

    public function vitals(){
        return $this->hasMany(Vital::class);
    }

    public function sessions(){
        return $this->hasMany(Session::class, 'patient_id', 'id');
    }
}


