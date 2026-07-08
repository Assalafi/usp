<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SsceResult extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'ssce_results';

    protected $fillable = [
        'user_id',
        'username',
        'ssce_id',
        'subject',
        'grade',
        'remark',
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
     * Get the applicant that owns this SSCE result
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'user_id', 'user_id');
    }

    /**
     * Get the SSCE exam that this result belongs to
     */
    public function ssce()
    {
        return $this->belongsTo(Ssce::class, 'ssce_id', 'id');
    }
}
