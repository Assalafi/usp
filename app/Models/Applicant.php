<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Applicant extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'applicants';

    protected $fillable = [
        'user_id',
        'username',
        'fullname',
        'surname',
        'first_name',
        'other_name',
        'gender',
        'nationality',
        'state',
        'lga',
        'city',
        'faculty',
        'department',
        'program',
        'mode',
        'session',
        'score',
        'status',
        'clearance',
        'cleared_by',
        'cleared_at',
        'action',
        'phone',
        'email',
        'address',
        'marital_status',
        'dob',
        'religion',
        'pob',
        'n_name',
        'n_relationship',
        'n_email',
        'n_phone',
        'n_address',
        's_name',
        's_phone',
        's_address',
        'submitted_at',
        // Admin decision fields
        'admission_date',
        'admission_remarks',
        'admitted_by',
        'rejection_date',
        'rejection_reason',
        'rejection_remarks',
        'rejected_by',
        'admitted'
    ];

    protected $casts = [
        'dob' => 'date',
        'score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'admission_date' => 'datetime',
        'rejection_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get all JAMB records for this applicant
     */
    public function jamb()
    {
        return $this->hasMany(Jamb::class, 'user_id', 'user_id');
    }

    /**
     * Get all SSCE records for this applicant
     */
    public function ssce()
    {
        return $this->hasMany(Ssce::class, 'user_id', 'user_id');
    }

    /**
     * Get all SSCE results for this applicant
     */
    public function ssceResults()
    {
        return $this->hasMany(SsceResult::class, 'user_id', 'user_id');
    }

    // belong to faculty
    public function facultys()
    {
        return $this->belongsTo(Faculty::class, 'faculty', 'code');
    }

    // department
    public function departments()
    {
        return $this->belongsTo(Department::class, 'department', 'code');
    }

    // program
    public function programs()
    {
        return $this->belongsTo(Program::class, 'program', 'code');
    }
}
