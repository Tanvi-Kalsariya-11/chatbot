<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'assistant_id',
        'invite_link'
    ];

    public function assistants() {
        return $this->belongsTo(Assistants::class, 'assistant_id');
    }

    public function users() {
        return $this->belongsToMany(User::class);
    }
}
