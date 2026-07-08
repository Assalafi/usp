<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ssce extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'ssce';

    protected $fillable = [
        'user_id',
        'username',
        'type',
        'year',
        'number',
        'center_name',
        'sitting'
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
     * Get the applicant that owns this SSCE record
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'user_id', 'user_id');
    }

    /**
     * Get all results for this SSCE exam
     */
    public function results()
    {
        return $this->hasMany(SsceResult::class, 'ssce_id', 'id');
    }
}
