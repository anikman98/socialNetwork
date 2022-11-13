<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Friends extends Model
{
    use HasFactory;

    protected $table = 'friends';

    protected $fillable = ['sender_id', 'receiver_id', 'status'];


    public function user(){
        return $this->belongsTo(User::class, 'sender_id');
    }
}
