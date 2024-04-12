<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    protected $fillable = ['number', 'type', 'score'];
    use HasFactory;

    public function lances()
    {
        return $this->hasMany(Lance::class, 'frame_id', 'number');
    }
}
