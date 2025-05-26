<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Machinery;

class Maintainance extends Model
{
    use HasFactory;

    protected $fillable = [
        'machinery_id',
        'user_id',
        'status',
        'maintainance_date',
        'remarks',
        'completed_date',
        'condition',
        'workDone',
        'expenses',
        'sourceOfFund',
        'conditionBeforeUse',
        'conditionAfterUse'
    ];
    
    // ✅ Each maintenance entry belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ✅ Each maintenance entry belongs to a machinery
    public function machinery()
    {
        return $this->belongsTo(Machinery::class, 'machinery_id');
    }
}
