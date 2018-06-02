[![Build Status](https://travis-ci.org/rennokki/schedule.svg?branch=master)](https://travis-ci.org/rennokki/schedule)
[![Latest Stable Version](https://poser.pugx.org/rennokki/schedule/v/stable)](https://packagist.org/packages/rennokki/schedule)
[![Total Downloads](https://poser.pugx.org/rennokki/schedule/downloads)](https://packagist.org/packages/rennokki/schedule)
[![Monthly Downloads](https://poser.pugx.org/rennokki/schedule/d/monthly)](https://packagist.org/packages/rennokki/schedule)
[![License](https://poser.pugx.org/rennokki/schedule/license)](https://packagist.org/packages/rennokki/schedule)

# Schedule
Schedule is a package that brings up features like "timetable" for a certain eloquent model.

To get started, let's use an example.

```php
// Let's get an user.
$user = User::find(1);

// We can set up a schedule for this user, Monday to Friday, between 8-12 and 13-18.
$user->setSchedule([
    'monday' => ['08:00-12:00', '13:00-18:00'],
    'tuesday' => ['08:00-12:00', '13:00-18:00'],
    'wednesday' => ['08:00-12:00', '13:00-18:00'],
    'thursday' => ['08:00-12:00', '13:00-18:00'],
    'friday' => ['08:00-12:00', '13:00-18:00'],
]);

// Let's also set exclusions, such as the user's birthday: 1st March, each year, the user is working from 8 to 12 only.
// Also, let's mark for the user the first and the second day of Christmas as having no schedule.
$user->setExclusions([
    '03-01' => ['08:00-12:00'],
    '12-25' => [],
    '12-26' => [],
    '2018-05-01' => [], // We can also set a specific day, just one.
]);

// We can check against day name, month and day, even year, month and day or Carbon instance.
$user->isAvailableOn('monday'); // true
$user->isAvailableOn('05-28'); // true; The year is the current year (2018); This is monday.
$user->isAvailableOn('2018-05-28'); // true
$user->isAvailableOn(Carbon::create(2018, 5, 28, 0, 0, 0)); // true

// We can do the opposite.
$user->isUnavailableOn('monday'); // false
$user->isUnavailableOn('05-28'); // false
$user->isUnavailableOn('2018-05-28'); // false
$user->isUnavailableOn(Carbon::create(2018, 5, 28, 0, 0, 0)); // false

// Check against exclusions is also provided.
$user->isUnavailableOn('12-25'); // true
$user->isUnavailableOn('03-01'); // false

// We can also check against time. For the sake of this example's length, this works too with the exclusions.
// The first parameter is the same as the first parameter in the isAvailableOn() method.
$user->isAvailableOnAt('monday', '09:00'); // true
$user->isUnavailableOnAt('monday', '09:00'); // false

// We can get the amount of working hours on a certain day.
// The first parameter is the same as the first parameter in the isAvailableOn() method.
$user->getHoursOn('03-01'); // 4
$user->getHoursOn('12-26'); // 0
$user->getHoursOn('05-28'); // 9
$user->getHoursOn('2018-05-28'); // 9

// Alternatively, you can have getMinutesOn() method.
$user->getMinutesOn('03-01'); // 240

// You can delete a schedule.
$user->deleteSchedule();

// You can also check if the user has a schedule set.
$user->hasSchedule(); // false, because we deleted it
```

# Installation
Install the package via Composer CLI:
```bash
$ composer require rennokki/schedule
```

For versions of Laravel that doesn't support package discovery, you should add this to your `config/app.php` file, in the `providers` array:

```php
\Rennokki\Schedule\ScheduleServiceProvider::class,
```

Publish the migration file and the config file.
```bash
$ php artisan vendor:publish
```

Migrate the database.
```bash
$ php artisan migrate
```

Add the trait to your model.
```php
use Rennokki\Schedule\Traits\HasSchedule;

class User extends Model {
    use HasSchedule;
    ...
}
```

# About the package
This package is inspired from [Spatie's Opening Hours](https://github.com/spatie/opening-hours) package, which uses a schedule but only statically, rather than binding it to a model.

Feel free to address the issues to the Issues Board if there are errors. Also, you can fork/pull request it and you can improve it anytime you want. Let's keep the open source alive!
