<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;

    public $table = 'users';
    protected $guarded = array();

    public function applicant()
    {
        return $this->hasOne(Applicant::class, 'user_id', 'id');
    }

    // Invoice
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'username', 'id');
    }
}
