<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeOfCourse extends Model
{
    use HasFactory;

    protected $table = 'change_of_course';

    protected $fillable = [
        'username',
        'user_id',
        'application_no',
        'student_name',
        'current_faculty',
        'current_department',
        'current_program',
        'new_faculty',
        'new_department',
        'new_program',
        'reason',
        'payment_status',
        'payment_rrr',
        'amount',
        'status',
        'new_hod_status',
        'new_hod_remark',
        'new_hod_date',
        'current_hod_status',
        'current_hod_remark',
        'current_hod_date',
        'dean_status',
        'dean_remark',
        'dean_date',
        'registrar_status',
        'registrar_remark',
        'registrar_date',
        'session',
        'submitted_at',
        'updated_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'new_hod_date' => 'datetime',
        'current_hod_date' => 'datetime',
        'dean_date' => 'datetime',
        'registrar_date' => 'datetime',
        'amount' => 'decimal:2'
    ];

    /**
     * Get the student that owns the change of course application.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'username', 'id_no');
    }

    /**
     * Get the user that owns the change of course application.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the current faculty details.
     */
    public function currentFaculty()
    {
        return $this->belongsTo(Faculty::class, 'current_faculty', 'code');
    }

    /**
     * Get the current department details.
     */
    public function currentDepartment()
    {
        return $this->belongsTo(Department::class, 'current_department', 'code');
    }

    /**
     * Get the current program details.
     */
    public function currentProgram()
    {
        return $this->belongsTo(Program::class, 'current_program', 'code');
    }

    /**
     * Get the new faculty details.
     */
    public function newFaculty()
    {
        return $this->belongsTo(Faculty::class, 'new_faculty', 'code');
    }

    /**
     * Get the new department details.
     */
    public function newDepartment()
    {
        return $this->belongsTo(Department::class, 'new_department', 'code');
    }

    /**
     * Get the new program details.
     */
    public function newProgram()
    {
        return $this->belongsTo(Program::class, 'new_program', 'code');
    }

    /**
     * Get the payment invoice for this application.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'payment_rrr', 'rrr');
    }

    /**
     * Scope a query to only include applications with a given status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include applications for a given session.
     */
    public function scopeForSession($query, $session)
    {
        return $query->where('session', $session);
    }

    /**
     * Scope a query to only include applications with paid status.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'Paid');
    }

    /**
     * Scope a query to only include pending applications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope a query to only include approved applications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    /**
     * Scope a query to only include rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    /**
     * Get the current approval step.
     */
    public function getCurrentStepAttribute()
    {
        if ($this->status === 'Payment Pending') {
            return 'Payment Required';
        } elseif ($this->status === 'Pending') {
            if ($this->new_hod_status === 'Pending') {
                return 'Awaiting New HOD';
            } elseif ($this->current_hod_status === 'Pending') {
                return 'Awaiting Current HOD';
            } elseif ($this->dean_status === 'Pending') {
                return 'Awaiting Dean';
            } elseif ($this->registrar_status === 'Pending') {
                return 'Awaiting Registrar';
            }
        } elseif ($this->status === 'Approved') {
            return 'Approved';
        } elseif ($this->status === 'Rejected') {
            return 'Rejected';
        }
        
        return 'Unknown';
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Payment Pending' => 'warning',
            'Pending' => 'info',
            'Approved' => 'success',
            'Rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the payment status badge color.
     */
    public function getPaymentStatusColorAttribute()
    {
        return match($this->payment_status) {
            'Paid' => 'success',
            'Pending' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Check if application can be edited by student.
     */
    public function canBeEditedByStudent()
    {
        return in_array($this->status, ['Payment Pending', 'Pending']) && 
               $this->new_hod_status === 'Pending';
    }

    /**
     * Check if application is complete.
     */
    public function isComplete()
    {
        return $this->status === 'Approved' && $this->payment_status === 'Paid';
    }

    /**
     * Get formatted application number.
     */
    public function getFormattedApplicationNoAttribute()
    {
        return $this->application_no;
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute()
    {
        return '₦' . number_format($this->amount, 2);
    }

    /**
     * Get submission date formatted.
     */
    public function getSubmissionDateAttribute()
    {
        return $this->submitted_at ? $this->submitted_at->format('d/m/Y h:i A') : 'N/A';
    }
}
