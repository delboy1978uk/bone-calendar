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
