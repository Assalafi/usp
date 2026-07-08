<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    public $table = 'students';
    protected $guarded = array();

    // student with Paid ID CARDS in Invoice table, for description ID CARDS and status Paid
    public function idCardPayment()
    {
        return $this->hasMany(Invoice::class, 'username', 'user_id')->where('description', 'ID CARDS')->where('status', 'Paid');
    }

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

    // Get course structure based on student's entry year
    public function courseStructure()
    {
        // Extract entry year from username (first 2 digits of session format: 15/07/02/054)
        $entryYear = '20' . substr($this->username, 0, 2);
        
        return $this->belongsTo(CourseStructure::class, 'structure_id', 'id')
            ->where(function ($query) use ($entryYear) {
                $query->where('from_session', '<=', $entryYear)
                      ->where('to_session', '>=', $entryYear);
            });
    }

    // Helper method to get structure_id directly
    public function getStructureIdAttribute()
    {
        // Extract entry year from username (first 2 digits of session format: 15/07/02/054)
        $entryYear = '20' . substr($this->username, 0, 2);
        
        $structure = CourseStructure::where('from_session', '<=', $entryYear)
                                   ->where('to_session', '>=', $entryYear)
                                   ->first();
        
        return $structure ? $structure->id : null;
    }

}
