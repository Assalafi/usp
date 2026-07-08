<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelPin extends Model
{
    use HasFactory;
    public $table = 'hostel_pin';
    protected $guarded = array();
}
