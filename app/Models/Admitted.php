<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admitted extends Model
{
    use HasFactory;
    public $table = 'admitted';
    protected $guarded = array();


    public function facultys()
    {
        return $this->belongsTo(Faculty::class, 'faculty', 'code');
    }

    public function departments()
    {
        return $this->belongsTo(Department::class, 'department', 'code');
    }

    public function programs()
    {
        return $this->belongsTo(Program::class, 'program', 'code');
    }
}
