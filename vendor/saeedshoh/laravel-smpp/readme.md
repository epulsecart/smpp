# Laravel SMPP
This package is a tiny wrapper for the [onlinecity/php-smpp](https://github.com/onlinecity/php-smpp) library.
It provides a basic SMPP interface and implementation for the Laravel framework.

## Installation
You can install Laravel SMPP using Composer command:
```bash
$ composer require saeedshoh/laravel-smpp
```

```php
<?php

namespace App\Http\Controllers;

class SmsController extends Controller
{
    public function send(SmppServiceInterface $smpp)
    {
        // One number
        $this->smpp->sendOne(112222900, 'Hi, this SMS was send via SMPP protocol');
        
        // Multiple numbers
        $this->smpp->sendBulk([1234567890, 0987654321], 'Hi!');
    }
}
```
# laravel-smpp
