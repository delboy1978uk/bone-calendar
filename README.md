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
### usage with google calendar
Limited functionality so far, but to do so, drop the following into your config (preferably create a 
`bone-calendar.php` settings file), and put your google json file into the keys folder. 
```php
putenv('GOOGLE_APPLICATION_CREDENTIALS=data/keys/google-service-account.json');

return [
    'bone-calendar' => [
        'channelId' => 'make_this_different_on_each_environment'
        'calendarId' => 'your-calendar-id@some-google-account.com',
        'callbackUrl' => 'https://your-site.com/api/calendar/google/callback',
        'syncTokenJsonPath' => 'data/keys/googleCalendarSyncToken.json',
    ], 
]
```
First thing is to register the webhook.
```
bone calendar:webhook
```
Perform a sync, which will store the first sync token that our callback endpoint will use. 
```
bone calendar:sync
```
@todo update google from db in sync, create callback functionality
