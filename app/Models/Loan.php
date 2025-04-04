<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'purpose',
        'user_id',
        'status',
        'loanDate',
        'repaymentDate',
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
