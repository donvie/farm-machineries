<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Rental;
use App\Models\Loan;
use App\Models\Maintainance;
use App\Models\Machinery;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rentals() {
        return $this->hasMany(Rental::class, 'user_id', 'id');
    }

    public function loans() {
        return $this->hasMany(Loan::class, 'user_id', 'id');
    }

    public function maintainances() {
        return $this->hasMany(Maintainance::class, 'user_id', 'id');
    }

    public function machinery()
    {
        return $this->belongsTo(Machinery::class, 'machinery_id');
    }
}
