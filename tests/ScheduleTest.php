<?php

namespace Rennokki\Schedule\Test;

use Carbon\Carbon;

class ScheduleTest extends TestCase
{
    protected $user;

    protected $availableDays = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday',
        'saturday', 'sunday',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(\Rennokki\Schedule\Test\Models\User::class)->create();
    }

    public function testCreateSchedule()
    {
        $this->assertFalse($this->user->hasSchedule());
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);
        $this->assertTrue($this->user->hasSchedule());
    }

    public function testCreateScheduleWhileAlreadyCreated()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue($this->user->hasSchedule());

        $this->user->setSchedule([
            'monday' => ['08:00-11:00'],
            'tuesday' => ['08:00-11:00', '16:00-20:00'],
            'wednesday' => [],
        ]);

        $this->assertTrue($this->user->isAvailableOn('monday'));
        $this->assertTrue($this->user->isAvailableOn('tuesday'));
        $this->assertFalse($this->user->isAvailableOn('wednesday'));
    }

    public function testUpdateSchedule()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->user->updateSchedule([
            'monday' => ['08:00-11:00'],
            'tuesday' => ['08:00-11:00', '16:00-20:00'],
            'wednesday' => [],
        ]);

        $this->assertTrue($this->user->isAvailableOn('monday'));
        $this->assertTrue($this->user->isAvailableOn('tuesday'));
        $this->assertFalse($this->user->isAvailableOn('wednesday'));
    }

    public function testEmptySchedule()
    {
        $this->user->setSchedule([]);

        foreach ($this->availableDays as $day) {
            $this->assertFalse($this->user->isAvailableOn($day));
            $this->assertEquals($this->user->getHoursOn($day), 0);
            $this->assertEquals($this->user->getMinutesOn($day), 0);
        }
    }

    public function testCreateExclusionsWithoutSchedule()
    {
        $this->assertFalse($this->user->hasSchedule());

        $this->assertFalse($this->user->setExclusions([
            '2018-05-28' => [],
            '2018-05-31' => ['08:00-20:00'],
            '06-01' => ['08:00-11:00'],
        ]));
    }

    public function testCreateExclusions()
    {
        $this->assertFalse($this->user->hasSchedule());

        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertEquals(count($this->user->setExclusions([
            '2018-05-28' => [],
            '2018-05-31' => ['08:00-20:00'],
            '06-01' => ['08:00-11:00'],
        ])), 3);
    }

    public function testUpdateExclusions()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertEquals(count($this->user->setExclusions([
            '2018-05-28' => [],
            '2018-05-31' => ['08:00-20:00'],
            '06-01' => ['08:00-11:00'],
        ])), 3);

        $this->assertEquals(count($this->user->updateExclusions([
            '2018-05-28' => [],
            '2018-05-31' => ['08:00-20:00'],
        ])), 2);
    }

    public function testDeleteSchedule()
    {
        $this->assertFalse($this->user->hasSchedule());
        $this->assertFalse($this->user->deleteSchedule());

        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue($this->user->hasSchedule());
        $this->assertTrue($this->user->deleteSchedule());
        $this->assertFalse($this->user->hasSchedule());
    }

    public function testIsAvailableOn()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue($this->user->isAvailableOn('monday'));
        $this->assertTrue($this->user->isAvailableOn('tuesday'));
        $this->assertTrue($this->user->isAvailableOn('wednesday'));
        $this->assertFalse($this->user->isAvailableOn('thursday'));
        $this->assertFalse($this->user->isAvailableOn('friday'));
        $this->assertFalse($this->user->isAvailableOn('saturday'));
        $this->assertFalse($this->user->isAvailableOn('sunday'));

        $this->assertTrue($this->user->isAvailableOn('2018-05-28'));
        $this->assertTrue($this->user->isAvailableOn('2018-05-29'));
        $this->assertTrue($this->user->isAvailableOn('2018-05-30'));
        $this->assertFalse($this->user->isAvailableOn('2018-05-31'));
        $this->assertFalse($this->user->isAvailableOn('2018-06-01'));
        $this->assertFalse($this->user->isAvailableOn('2018-06-02'));
        $this->assertFalse($this->user->isAvailableOn('2018-06-03'));

        $this->assertTrue($this->user->isAvailableOn(Carbon::create(2018, 5, 28, 0, 0, 0)));
        $this->assertTrue($this->user->isAvailableOn(Carbon::create(2018, 5, 29, 0, 0, 0)));
        $this->assertTrue($this->user->isAvailableOn(Carbon::create(2018, 5, 30, 0, 0, 0)));
        $this->assertFalse($this->user->isAvailableOn(Carbon::create(2018, 5, 31, 0, 0, 0)));
        $this->assertFalse($this->user->isAvailableOn(Carbon::create(2018, 6, 1, 0, 0, 0)));
        $this->assertFalse($this->user->isAvailableOn(Carbon::create(2018, 6, 2, 0, 0, 0)));
        $this->assertFalse($this->user->isAvailableOn(Carbon::create(2018, 6, 3, 0, 0, 0)));
    }

    public function testIsUnavailableOn()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue(! $this->user->isUnavailableOn('monday'));
        $this->assertTrue(! $this->user->isUnavailableOn('tuesday'));
        $this->assertTrue(! $this->user->isUnavailableOn('wednesday'));
        $this->assertFalse(! $this->user->isUnavailableOn('thursday'));
        $this->assertFalse(! $this->user->isUnavailableOn('friday'));
        $this->assertFalse(! $this->user->isUnavailableOn('saturday'));
        $this->assertFalse(! $this->user->isUnavailableOn('sunday'));

        $this->assertTrue(! $this->user->isUnavailableOn('2018-05-28'));
        $this->assertTrue(! $this->user->isUnavailableOn('2018-05-29'));
        $this->assertTrue(! $this->user->isUnavailableOn('2018-05-30'));
        $this->assertFalse(! $this->user->isUnavailableOn('2018-05-31'));
        $this->assertFalse(! $this->user->isUnavailableOn('2018-06-01'));
        $this->assertFalse(! $this->user->isUnavailableOn('2018-06-02'));
        $this->assertFalse(! $this->user->isUnavailableOn('2018-06-03'));

        $this->assertTrue(! $this->user->isUnavailableOn(Carbon::create(2018, 5, 28, 0, 0, 0)));
        $this->assertTrue(! $this->user->isUnavailableOn(Carbon::create(2018, 5, 29, 0, 0, 0)));
        $this->assertTrue(! $this->user->isUnavailableOn(Carbon::create(2018, 5, 30, 0, 0, 0)));
        $this->assertFalse(! $this->user->isUnavailableOn(Carbon::create(2018, 5, 31, 0, 0, 0)));
        $this->assertFalse(! $this->user->isUnavailableOn(Carbon::create(2018, 6, 1, 0, 0, 0)));
        $this->assertFalse(! $this->user->isUnavailableOn(Carbon::create(2018, 6, 2, 0, 0, 0)));
        $this->assertFalse(! $this->user->isUnavailableOn(Carbon::create(2018, 6, 3, 0, 0, 0)));
    }

    public function testIsAvailableOnAt()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue($this->user->isAvailableOnAt('monday', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('monday', '07:59'));
        $this->assertFalse($this->user->isAvailableOnAt('monday', '12:01'));
        $this->assertFalse($this->user->isAvailableOnAt('monday', '13:00'));
        $this->assertTrue($this->user->isAvailableOnAt('tuesday', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('tuesday', '07:59'));
        $this->assertFalse($this->user->isAvailableOnAt('tuesday', '12:01'));
        $this->assertFalse($this->user->isAvailableOnAt('tuesday', '13:00'));
        $this->assertFalse($this->user->isAvailableOnAt('tuesday', '15:59'));
        $this->assertTrue($this->user->isAvailableOnAt('tuesday', '16:00'));
        $this->assertTrue($this->user->isAvailableOnAt('tuesday', '16:01'));
        $this->assertTrue($this->user->isAvailableOnAt('tuesday', '19:59'));
        $this->assertTrue($this->user->isAvailableOnAt('tuesday', '20:00'));
        $this->assertFalse($this->user->isAvailableOnAt('tuesday', '21:01'));

        $this->assertTrue($this->user->isAvailableOnAt('2018-05-28', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-28', '07:59'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-28', '12:01'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-28', '13:00'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-29', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-29', '07:59'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-29', '12:01'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-29', '13:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-29', '15:59'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-29', '16:00'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-29', '16:01'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-29', '19:59'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-29', '20:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-29', '21:01'));

        $this->assertTrue($this->user->isAvailableOnAt(Carbon::create(2018, 5, 28, 0, 0, 0), '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt(Carbon::create(2018, 5, 28, 0, 0, 0), '07:59'));
        $this->assertFalse($this->user->isAvailableOnAt(Carbon::create(2018, 5, 28, 0, 0, 0), '12:01'));
        $this->assertFalse($this->user->isAvailableOnAt(Carbon::create(2018, 5, 28, 0, 0, 0), '13:00'));
        $this->assertTrue($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '07:59'));
        $this->assertFalse($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '12:01'));
        $this->assertFalse($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '13:00'));
        $this->assertFalse($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '15:59'));
        $this->assertTrue($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '16:00'));
        $this->assertTrue($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '16:01'));
        $this->assertTrue($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '19:59'));
        $this->assertTrue($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '20:00'));
        $this->assertFalse($this->user->isAvailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '21:01'));

        $this->assertFalse($this->user->isAvailableOnAt('thursday', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('thursday', '09:00'));
        $this->assertFalse($this->user->isAvailableOnAt('thursday', '10:30'));
        $this->assertFalse($this->user->isAvailableOnAt('friday', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('friday', '09:00'));
        $this->assertFalse($this->user->isAvailableOnAt('friday', '10:30'));
        $this->assertFalse($this->user->isAvailableOnAt('saturday', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('saturday', '09:00'));
        $this->assertFalse($this->user->isAvailableOnAt('saturday', '10:30'));
        $this->assertFalse($this->user->isAvailableOnAt('sunday', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('sunday', '09:00'));
        $this->assertFalse($this->user->isAvailableOnAt('sunday', '10:30'));
    }

    public function testIsUnavailableOnAt()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue(! $this->user->isUnavailableOnAt('monday', '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('monday', '07:59'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('monday', '12:01'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('monday', '13:00'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('tuesday', '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('tuesday', '07:59'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('tuesday', '12:01'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('tuesday', '13:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('tuesday', '15:59'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('tuesday', '16:00'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('tuesday', '16:01'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('tuesday', '19:59'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('tuesday', '20:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('tuesday', '21:01'));

        $this->assertTrue(! $this->user->isUnavailableOnAt('2018-05-28', '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('2018-05-28', '07:59'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('2018-05-28', '12:01'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('2018-05-28', '13:00'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('2018-05-29', '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('2018-05-29', '07:59'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('2018-05-29', '12:01'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('2018-05-29', '13:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('2018-05-29', '15:59'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('2018-05-29', '16:00'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('2018-05-29', '16:01'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('2018-05-29', '19:59'));
        $this->assertTrue(! $this->user->isUnavailableOnAt('2018-05-29', '20:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('2018-05-29', '21:01'));

        $this->assertTrue(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 28, 0, 0, 0), '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 28, 0, 0, 0), '07:59'));
        $this->assertFalse(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 28, 0, 0, 0), '12:01'));
        $this->assertFalse(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 28, 0, 0, 0), '13:00'));
        $this->assertTrue(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '07:59'));
        $this->assertFalse(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '12:01'));
        $this->assertFalse(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '13:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '15:59'));
        $this->assertTrue(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '16:00'));
        $this->assertTrue(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '16:01'));
        $this->assertTrue(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '19:59'));
        $this->assertTrue(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '20:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt(Carbon::create(2018, 5, 29, 0, 0, 0), '21:01'));

        $this->assertFalse(! $this->user->isUnavailableOnAt('thursday', '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('thursday', '09:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('thursday', '10:30'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('friday', '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('friday', '09:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('friday', '10:30'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('saturday', '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('saturday', '09:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('saturday', '10:30'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('sunday', '08:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('sunday', '09:00'));
        $this->assertFalse(! $this->user->isUnavailableOnAt('sunday', '10:30'));
    }

    public function testIsAvailableOnExclusion()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue($this->user->isAvailableOn('monday'));
        $this->assertFalse($this->user->isAvailableOn('thursday'));

        $this->user->setExclusions([
            '2018-05-28' => [], // monday, 2018
            '2018-05-31' => ['08:00-20:00'], // thursday, 2018
            '06-01' => ['08:00-11:00'], // friday, 2018
        ]);

        $this->assertFalse($this->user->isAvailableOn('2018-05-28'));
        $this->assertTrue($this->user->isAvailableOn('2018-05-31'));
        $this->assertTrue($this->user->isAvailableOn('2018-06-01'));
        $this->assertFalse($this->user->isAvailableOn('2018-06-02'));

        $this->assertEquals($this->user->getHoursOn('2018-05-28'), 0);
        $this->assertEquals($this->user->getHoursOn('2018-05-31'), 12);
        $this->assertEquals($this->user->getHoursOn('2018-06-01'), 3);
        $this->assertEquals($this->user->getHoursOn('2018-06-02'), 0);

        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-05-28'), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-05-31'), 12);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-06-01'), 3);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-06-02'), 0);
    }

    public function testIsUnavailableOnExclusion()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue(! $this->user->isUnavailableOn('monday'));
        $this->assertFalse(! $this->user->isUnavailableOn('thursday'));

        $this->user->setExclusions([
            '2018-05-28' => [], // monday, 2018
            '2018-05-31' => ['08:00-20:00'], // thursday, 2018
            '06-01' => ['08:00-11:00'], // friday, 2018
        ]);

        $this->assertFalse(! $this->user->isUnavailableOn('2018-05-28'));
        $this->assertTrue(! $this->user->isUnavailableOn('2018-05-31'));
        $this->assertTrue(! $this->user->isUnavailableOn('2018-06-01'));
        $this->assertFalse(! $this->user->isUnavailableOn('2018-06-02'));

        $this->assertEquals($this->user->getHoursOn('2018-05-28'), 0);
        $this->assertEquals($this->user->getHoursOn('2018-05-31'), 12);
        $this->assertEquals($this->user->getHoursOn('2018-06-01'), 3);
        $this->assertEquals($this->user->getHoursOn('2018-06-02'), 0);

        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-05-28'), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-05-31'), 12);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-06-01'), 3);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-06-02'), 0);
    }

    public function testIsAvailableOnAtExclusion()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertTrue($this->user->isAvailableOn('monday'));
        $this->assertFalse($this->user->isAvailableOn('thursday'));

        $this->user->setExclusions([
            '2018-05-28' => [], // monday, 2018
            '2018-05-31' => ['08:00-20:00'], // thursday, 2018
            '06-01' => ['08:00-11:00'], // friday, 2018
        ]);

        $this->assertFalse($this->user->isAvailableOnAt('2018-05-28', '00:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-28', '12:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-28', '15:30'));

        $this->assertTrue($this->user->isAvailableOnAt('2018-05-31', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-31', '07:59'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-31', '08:01'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-31', '09:00'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-31', '20:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-05-31', '20:01'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-05-31', '19:59'));

        $this->assertTrue($this->user->isAvailableOnAt('2018-06-01', '08:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-06-01', '07:59'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-06-01', '08:01'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-06-01', '09:00'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-06-01', '11:00'));
        $this->assertFalse($this->user->isAvailableOnAt('2018-06-01', '11:01'));
        $this->assertTrue($this->user->isAvailableOnAt('2018-06-01', '10:59'));
    }

    public function testGetHoursOn()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertEquals($this->user->getHoursOn('monday'), 4);
        $this->assertEquals($this->user->getHoursOn('tuesday'), 8);
        $this->assertEquals($this->user->getHoursOn('wednesday'), 1);
        $this->assertEquals($this->user->getHoursOn('thursday'), 0);
        $this->assertEquals($this->user->getHoursOn('friday'), 0);
        $this->assertEquals($this->user->getHoursOn('saturday'), 0);
        $this->assertEquals($this->user->getHoursOn('sunday'), 0);

        $this->assertEquals($this->user->schedule()->first()->getHoursOn('monday'), 4);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('tuesday'), 8);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('wednesday'), 1);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('thursday'), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('friday'), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('saturday'), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('sunday'), 0);

        $this->assertEquals($this->user->getHoursOn('2018-05-28'), 4);
        $this->assertEquals($this->user->getHoursOn('2018-05-29'), 8);
        $this->assertEquals($this->user->getHoursOn('2018-05-30'), 1);
        $this->assertEquals($this->user->getHoursOn('2018-05-31'), 0);
        $this->assertEquals($this->user->getHoursOn('2018-06-01'), 0);
        $this->assertEquals($this->user->getHoursOn('2018-06-02'), 0);
        $this->assertEquals($this->user->getHoursOn('2018-06-03'), 0);

        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-05-28'), 4);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-05-29'), 8);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-05-30'), 1);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-05-31'), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-06-01'), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-06-02'), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn('2018-06-03'), 0);

        $this->assertEquals($this->user->getHoursOn(Carbon::create(2018, 5, 28, 0, 0, 0)), 4);
        $this->assertEquals($this->user->getHoursOn(Carbon::create(2018, 5, 29, 0, 0, 0)), 8);
        $this->assertEquals($this->user->getHoursOn(Carbon::create(2018, 5, 30, 0, 0, 0)), 1);
        $this->assertEquals($this->user->getHoursOn(Carbon::create(2018, 5, 31, 0, 0, 0)), 0);
        $this->assertEquals($this->user->getHoursOn(Carbon::create(2018, 6, 1, 0, 0, 0)), 0);
        $this->assertEquals($this->user->getHoursOn(Carbon::create(2018, 6, 2, 0, 0, 0)), 0);
        $this->assertEquals($this->user->getHoursOn(Carbon::create(2018, 6, 3, 0, 0, 0)), 0);

        $this->assertEquals($this->user->schedule()->first()->getHoursOn(Carbon::create(2018, 5, 28, 0, 0, 0)), 4);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn(Carbon::create(2018, 5, 29, 0, 0, 0)), 8);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn(Carbon::create(2018, 5, 30, 0, 0, 0)), 1);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn(Carbon::create(2018, 5, 31, 0, 0, 0)), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn(Carbon::create(2018, 6, 1, 0, 0, 0)), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn(Carbon::create(2018, 6, 2, 0, 0, 0)), 0);
        $this->assertEquals($this->user->schedule()->first()->getHoursOn(Carbon::create(2018, 6, 3, 0, 0, 0)), 0);
    }

    public function testGetMinutesOn()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->user->setExclusions([
            '2018-05-28' => [], // monday, 2018
            '2018-05-31' => ['08:00-20:00'], // thursday, 2018
            '06-01' => ['08:00-11:00'], // friday, 2018
        ]);

        $this->assertEquals($this->user->getMinutesOn('monday'), 4 * 60);
        $this->assertEquals($this->user->getMinutesOn('tuesday'), 8 * 60);
        $this->assertEquals($this->user->getMinutesOn('wednesday'), 1 * 60);
        $this->assertEquals($this->user->getMinutesOn('thursday'), 0);
        $this->assertEquals($this->user->getMinutesOn('friday'), 0);
        $this->assertEquals($this->user->getMinutesOn('saturday'), 0);
        $this->assertEquals($this->user->getMinutesOn('sunday'), 0);

        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('monday'), 4 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('tuesday'), 8 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('wednesday'), 1 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('thursday'), 0);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('friday'), 0);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('saturday'), 0);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('sunday'), 0);

        $this->assertEquals($this->user->getMinutesOn('2018-05-28'), 0);
        $this->assertEquals($this->user->getMinutesOn('2018-05-29'), 8 * 60);
        $this->assertEquals($this->user->getMinutesOn('2018-05-30'), 1 * 60);
        $this->assertEquals($this->user->getMinutesOn('2018-05-31'), 12 * 60);
        $this->assertEquals($this->user->getMinutesOn('2018-06-01'), 3 * 60);
        $this->assertEquals($this->user->getMinutesOn('2018-06-02'), 0);
        $this->assertEquals($this->user->getMinutesOn('2018-06-03'), 0);

        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('2018-05-28'), 0);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('2018-05-29'), 8 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('2018-05-30'), 1 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('2018-05-31'), 12 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('2018-06-01'), 3 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('2018-06-02'), 0);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn('2018-06-03'), 0);

        $this->assertEquals($this->user->getMinutesOn(Carbon::create(2018, 5, 28, 0, 0, 0)), 0);
        $this->assertEquals($this->user->getMinutesOn(Carbon::create(2018, 5, 29, 0, 0, 0)), 8 * 60);
        $this->assertEquals($this->user->getMinutesOn(Carbon::create(2018, 5, 30, 0, 0, 0)), 1 * 60);
        $this->assertEquals($this->user->getMinutesOn(Carbon::create(2018, 5, 31, 0, 0, 0)), 12 * 60);
        $this->assertEquals($this->user->getMinutesOn(Carbon::create(2018, 6, 1, 0, 0, 0)), 3 * 60);
        $this->assertEquals($this->user->getMinutesOn(Carbon::create(2018, 6, 2, 0, 0, 0)), 0);
        $this->assertEquals($this->user->getMinutesOn(Carbon::create(2018, 6, 3, 0, 0, 0)), 0);

        $this->assertEquals($this->user->schedule()->first()->getMinutesOn(Carbon::create(2018, 5, 28, 0, 0, 0)), 0);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn(Carbon::create(2018, 5, 29, 0, 0, 0)), 8 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn(Carbon::create(2018, 5, 30, 0, 0, 0)), 1 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn(Carbon::create(2018, 5, 31, 0, 0, 0)), 12 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn(Carbon::create(2018, 6, 1, 0, 0, 0)), 3 * 60);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn(Carbon::create(2018, 6, 2, 0, 0, 0)), 0);
        $this->assertEquals($this->user->schedule()->first()->getMinutesOn(Carbon::create(2018, 6, 3, 0, 0, 0)), 0);
    }

    public function testDeleteExclusions()
    {
        $this->assertfalse($this->user->deleteExclusions());

        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->user->setExclusions([
            '2018-05-28' => [], // monday, 2018
            '2018-05-31' => ['08:00-20:00'], // thursday, 2018
            '06-01' => ['08:00-11:00'], // friday, 2018
        ]);

        $this->assertEquals(count($this->user->getExclusions()), 3);
        $this->assertEquals(count($this->user->deleteExclusions()), 0);
    }

    public function testIsAvailableOnWrongArgument()
    {
        $this->user->setSchedule([
            'monday' => ['08:00-12:00'],
            'tuesday' => ['08:00-12:00', '16:00-20:00'],
            'wednesday' => ['08:00-09:00'],
        ]);

        $this->assertFalse($this->user->isAvailableOn('just_a_random_day'));
    }
}
