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
        'rent',
        'otherExpenses',
        'completedDate',
        'operator_id',
        'condition',
        'hectare',
        'rentFee',
        'attachment',
        'totalHarvest',
        'startDate'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function operator() // Add this relationship
    {
        return $this->belongsTo(User::class, 'operator_id'); // Specify the foreign key
    }

    public function machinery()
    {
        return $this->belongsTo(Machinery::class);
    }
}
