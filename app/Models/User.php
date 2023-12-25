<?php

namespace App\Models;

use App\Models\Group;
use App\Enums\RoleType;
use App\Models\GroupHistory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // protected $appends = [
    //     'formatted_created_at',
    // ];

    // public function getFormattedCreatedAtAttribute()
    // {
    //     return $this->created_at->format(config('app.date_format'));
    // }


    public function bill()
    {
        return $this->hasMany(Bill::class);
    }

    public function debt()
    {
        return $this->hasMany(Debt::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
