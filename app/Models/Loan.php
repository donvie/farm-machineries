<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'amount',
        // 'purpose',
        'user_id',
        'status',
        'loanDate',
        'repaymentDate',
        'remarks',
        'loans',
        'histories',
        'qty',
        'attachments',
        'dateOfRelease',
        'loanGrantedDate',
        'isFullPayment'
    ];

    protected $casts = [
        'loans' => 'array', // <-- This automatically json_encodes / decodes
        'histories' => 'array', // <-- This automatically json_encodes / decodes
        'attachments' => 'array', // <-- add this line
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
