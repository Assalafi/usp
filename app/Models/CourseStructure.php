<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseStructure extends Model
{
    use HasFactory;
    public $table = 'course_structure';
    protected $guarded = array();
}
