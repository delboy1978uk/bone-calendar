# calendar
Calendar package for Bone Mvc Framework
## installation
Use Composer
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