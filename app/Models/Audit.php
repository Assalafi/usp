<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = [
        'username',
        'acc_type',
        'appointment',
        'page',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'payload',
    ];
}
