<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'ass_id',
        'group_id',
        'thread_name',
        'thread_id',
        'assistant_id'
    ];
    
    protected function users() {
        return $this->belongsToMany(User::class);
    }

    protected function assistants() {
        return $this->belongsTo(Assistants::class, 'ass_id');
    }
}
