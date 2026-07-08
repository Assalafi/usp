<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectionCandidate extends Model
{
    use HasFactory;

    public $table = 'election_candidates';
    protected $guarded = array();
}
