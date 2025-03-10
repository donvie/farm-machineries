<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Maintainance;

class Machinery extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_name', 'type', 'year_acquired', 
        'status', 
        'last_maintenance_date', 'next_scheduled_maintenance', 'image', 'isDeactivate'
    ];

    /**
     * Get the machines that belong to this machinery.
     */
    public function maintainances()
    {
        return $this->hasMany(Maintainance::class, 'machinery_id', 'id'); // ✅ Define Relationship
    }
}
