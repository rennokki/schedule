<?php

namespace Rennokki\Schedule\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Rennokki\Schedule\Traits\HasSchedule;

class User extends Model
{
    use HasSchedule;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
