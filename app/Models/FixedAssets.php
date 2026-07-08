<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAssets extends Model
{
    use HasFactory;
    public $table = 'fixed_assets';
    protected $guarded = array();
}
