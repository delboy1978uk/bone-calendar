# calendar
Calendar package for Bone Mvc Framework
## installation
Use Composer. Requires `delboy1978uk/bone-user` to be installed and configured. (See htps://github.com/delboy1978uk/bone-user for details)
```
composer require delboy1978uk/bone-calendar
```
## usage
Simply add to the `config/packages.php`
```php
<?php

// use statements here
use Bone\Calendar\CalendarPackage;

return [
    'packages' => [
        // packages here...,
        CalendarPackage::class,
    ],
    // ...
];
```
### usage with gfoogle calendar
Limited functionality so far, but to do so, drop the following into your config (preferably create a 
`bone-calendar.php` settings file), and put your google json file into the keys folder.
```php
putenv('GOOGLE_APPLICATION_CREDENTIALS=data/keys/google-service-account.json');

return [
    'bone-calendar' => [
        'calendarId' => 'your-calendar-id@some-google-account.com',
        'callbackUrl' => 'https://your-site.com/api/calendar/google/callback',
    ], 
]
```
You 
