<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Maintainance;
use App\Models\Rental;
use App\Models\Technician;

class Machinery extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_name', 'type', 'year_acquired', 
        'status', 
        'last_maintenance_date', 'next_scheduled_maintenance', 'image', 'isDeactivate',
        'brand',
        'serial',
        'capacity',
        'accessories',
        'supplier',
        'branchAddress',
        'primeMoverYearAcquired',
        'primeMoverBrand',
        'primeMoverSerial',
        'primeMoverRatedPower',
        // 'costPerMachine',
        'condition',
        'workDone',
        'expenses',
        'count'
    ];

    /**
     * Get the machines that belong to this machinery.
     */
    public function maintainances()
    {
        return $this->hasMany(Maintainance::class, 'machinery_id', 'id'); // ✅ Define Relationship
    }
    

    public function rentals() {
        return $this->hasMany(Rental::class, 'machinery_id', 'id');
    }

    
    public function technicians() {
        return $this->hasMany(Technician::class, 'machinery_id', 'id');
    }
}
