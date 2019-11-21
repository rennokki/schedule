<?php

namespace Rennokki\Schedule;

use Carbon\Carbon;

class TimeRange
{
    protected $timeRangeString;
    protected $carbonInstance = Carbon::class;

    /**
     * Create a new TimeRange instance.
     *
     * @param string $timeRangeString
     * @param Carbon $carbonInstance
     * @return void
     */
    public function __construct($timeRangeString, $carbonInstance = null)
    {
        $this->timeRangeString = $timeRangeString;

        if ($carbonInstance) {
            $this->carbonInstance = $carbonInstance;
        }
    }

    /**
     * Check if the string provided is a TimeRange formatted string.
     *
     * @return bool
     */
    public function isValidTimeRange(): bool
    {
        return (bool) preg_match_all('/(([0-1])([0-9])|(2)([0-3])):([0-5])([0-9])-(([0-1])([0-9])|(2)([0-3])):([0-5])([0-9])/', $this->timeRangeString);
    }

    /**
     * Check if the string provided is a valid hour:minute formatted string.
     *
     * @return bool
     */
    public function isValidHourMinute($hourMinute): bool
    {
        return (bool) preg_match_all('/(([0-1])([0-9])|(2)([0-3])):([0-5])([0-9])/', $hourMinute);
    }

    /**
     * Check if a specific hour:minute is in the timerange.
     *
     * @param string $hourMinute The time in format hour:minute
     * @return bool
     */
    public function isInTimeRange($hourMinute): bool
    {
        if (! $this->isValidHourMinute($hourMinute)) {
            return false;
        }

        [$hour, $minute] = explode(':', $hourMinute);
        $hour = (int) $hour;
        $minute = (int) $minute;

        $date = $this->carbonInstance::create(2018, 1, 1, $hour, $minute, 0);

        return (bool) ($date->greaterThanOrEqualTo($this->getStartCarbonInstance()) && $date->lessThanOrEqualTo($this->getEndCarbonInstance()));
    }

    /**
     * Get the start Carbon instance as 1st January 2018, and a specified hour.
     *
     * @return Carbon
     */
    public function getStartCarbonInstance()
    {
        return $this->carbonInstance::create(2018, 1, 1, $this->getStartHour(), $this->getStartMinute(), 0);
    }

    /**
     * Get the end Carbon instance as 1st/2nd January 2018, at the right hour and minute.
     *
     * @return Carbon
     */
    public function getEndCarbonInstance()
    {
        return $this->carbonInstance::create(2018, 1, 1, $this->getEndHour(), $this->getEndMinute(), 0);
    }

    /**
     * Difference in hours between the both ends.
     *
     * @return int The difference, in hours.
     */
    public function diffInHours(): int
    {
        if (! $this->isValidTimeRange()) {
            return (int) 0;
        }

        return (int) $this->getStartCarbonInstance()->diffInHours($this->getEndCarbonInstance());
    }

    /**
     * Difference in minutes between the both ends.
     *
     * @return int The difference, in minutes.
     */
    public function diffInMinutes(): int
    {
        if (! $this->isValidTimeRange()) {
            return (int) 0;
        }

        return (int) $this->getStartCarbonInstance()->diffInMinutes($this->getEndCarbonInstance());
    }

    /**
     * Get the hour of the starting part.
     *
     * @return int
     */
    public function getStartHour(): ?int
    {
        if (! $this->isValidTimeRange()) {
            return null;
        }

        return (int) explode(':', $this->toArray()[0])[0];
    }

    /**
     * Get the minute of the starting part.
     *
     * @return int
     */
    public function getStartMinute(): ?int
    {
        if (! $this->isValidTimeRange()) {
            return null;
        }

        return (int) explode(':', $this->toArray()[0])[1];
    }

    /**
     * Get the hour of the ending part.
     *
     * @return int
     */
    public function getEndHour(): ?int
    {
        if (! $this->isValidTimeRange()) {
            return null;
        }

        return (int) explode(':', $this->toArray()[1])[0];
    }

    /**
     * Get the minute of the ending part.
     *
     * @return int
     */
    public function getEndMinute(): ?int
    {
        if (! $this->isValidTimeRange()) {
            return null;
        }

        return (int) explode(':', $this->toArray()[1])[1];
    }

    /**
     * Get the time range in array format.
     * This will return something like ['08:00', '17:00']
     * Use get[Start|End][Hour|Minute]() method to get the hours as integers.
     *
     * @return array
     */
    public function toArray(): array
    {
        if (! $this->isValidTimeRange()) {
            return [];
        }

        return (array) explode('-', $this->timeRangeString);
    }
}
