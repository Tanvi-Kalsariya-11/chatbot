<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assistants extends Model
{
    use HasFactory;

    protected $fillable = [
        'assistant_id',
        'user_id',
        'default'
    ];

    protected function users() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
