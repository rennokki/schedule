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

    public function getHoursOn($dateOrDay): int
    {
        return $this->model()->first()->getHoursOn($dateOrDay);
    }

    public function getMinutesOn($dateOrDay): int
    {
        return $this->model()->first()->getMinutesOn($dateOrDay);
    }
}
