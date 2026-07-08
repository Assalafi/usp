<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionHistory extends Model
{
    use HasFactory;
    
    protected $table = 'session_history';
    
    protected $fillable = [
        'username',
        'session',
        'level',
        'total_unit',
        'product',
        'cgpa',
        'status'
    ];
}
