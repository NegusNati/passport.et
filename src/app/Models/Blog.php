<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'published_at' => 'datetime'
    ];

    public function user()
    {
       
        return $this->belongsTo(User::class);
    }
}
