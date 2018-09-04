<?php

namespace Rennokki\Schedule\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleModel extends Model
{
    protected $table = 'schedules';
    protected $guarded = [];
    protected $casts = [
        'schedule' => 'array',
        'exclusions' => 'array',
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
