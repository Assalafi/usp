<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    public $table = 'invoices';
    protected $guarded = array();

    // has relation with students table, invoices.username is link to students.user_id
    public function student()
    {
        return $this->belongsTo(Student::class, 'username', 'user_id');
    }
    // all users
    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'id');
    }
    // staff where users.acc_type = 'Staff', link with staff table, to get more records
    public function staff()
    {
        return $this->belongsTo(User::class, 'username', 'id')->where('acc_type', 'Staff');
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
}
