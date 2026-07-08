<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;
    public $table = 'program';
    protected $guarded = array();


    public function facultys(){
        return $this->belongsTo(Faculty::class,'faculty','code');
    }
    public function depts(){
        return $this->belongsTo(Department::class,'department','code');
    }
}
