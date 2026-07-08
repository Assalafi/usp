<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DEQualification extends Model
{
    use HasFactory;
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'de_qualifications';

    protected $fillable = [
        'id',
        'user_id',
        'applicant_id',
        'qualification_type',
        'institution',
        'grad_year',
        'grade',
        'reg_number',
        'credentials_path',
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

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
