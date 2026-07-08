<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;
    public $table = 'faculty';
    protected $guarded = array();

    // has many departments relation faculty in departments is the foreign key of code in faculty
    public function departments(){
        return $this->hasMany(Department::class,'faculty','code');
    }
    // also has many program
    public function programs(){
        return $this->hasMany(Program::class,'faculty','code');
    }

}
