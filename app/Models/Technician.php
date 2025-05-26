<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Machinery;
class Technician extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'remarks',
        'machinery_id',
        'startDate',
        'completedDate',
        'fields' // ✅ Include this
    ];

    public function machinery()
    {
        return $this->belongsTo(Machinery::class, 'machinery_id');
    }
}
