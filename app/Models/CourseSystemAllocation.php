<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSystemAllocation extends Model
{
    use HasFactory;
    public $table = 'course_system_allocation';
    protected $guarded = array();

    // belong to department table using department_id
    public function departments()
    {
        return $this->belongsTo(Department::class, 'department', 'code');
    }

    // and department belong to faculty table using faculty_id, but i dont have faculty_id in course_system_allocation table
    public function facultys()
    {
        return $this->departments()->belongsTo(Faculty::class, 'faculty', 'code');
    }
}
