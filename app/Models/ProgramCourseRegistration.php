<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramCourseRegistration extends Model
{
    use HasFactory;
    public $table = 'program_course_registration';
    protected $guarded = array();

    // belong to CourseStructure using structure_id
    public function structure()
    {
        return $this->belongsTo(CourseStructure::class, 'structure_id', 'id');
    }
    
}
