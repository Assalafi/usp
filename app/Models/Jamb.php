<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Jamb extends Model
{
    use HasFactory;
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'jamb';

    protected $fillable = [
        'user_id',
        'username',
        'subject',
        'score'
    ];

    protected $casts = [
        'score' => 'decimal:2',
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
     * Get the applicant that owns this JAMB record
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'user_id', 'user_id');
    }
}
