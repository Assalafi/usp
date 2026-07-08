<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    public $table = 'department';
    protected $guarded = array();

    // belong to faculty relation faculty is the column in department table and is representing code in faculty

    public function faculty(){
        return $this->belongsTo(Faculty::class,'faculty','code');
    }
}
