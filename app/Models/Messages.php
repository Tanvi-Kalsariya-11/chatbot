<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messages extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'thread_id',
        'group_id',
        'user_id',
        'message',
        'role',
    ];

    public function thread() {
        return $this->belongsTo(Thread::class, 'thread_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function group() {
        return  $this->belongsTo(Group::class, 'group_id');
    }
}
