<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;
    

    protected $fillable = [
        'user_id',
        'machinery_id',
        'status',
        'date_of_rent',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function machinery()
    {
        return $this->belongsTo(Machinery::class);
    }
}
