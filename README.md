# Recurr [![Build Status](https://travis-ci.org/simshaun/recurr.png)](https://travis-ci.org/simshaun/recurr.png)

Recurr is a PHP library for working with recurrence rules ([RRULE](http://tools.ietf.org/html/rfc2445)) and converting them in to DateTime objects.

Recurr was developed as a precursor for a calendar with recurring events, and is heavily inspired by [rrule.js](https://github.com/jkbr/rrule).

Installation
------------

Recurr is hosted on [packagist](http://packagist.org), meaning you can install
it with [Composer](http://getcomposer.org/).

Create a composer.json file

```json
{
    "require": {
        "simshaun/recurr": "dev-master"
    }
}
```

Install composer and run it

```sh
wget http://getcomposer.org/composer.phar
php composer.phar install
```

(Optional) Autoload Recurr

```php
require 'vendor/autoload.php';
```


Demo
-----------

```php
$timezone    = 'America/New_York';
$startDate   = new \DateTime('2013-06-12 20:00:00', new \DateTimeZone($timezone));
$rule        = new \Recurr\RecurrenceRule('FREQ=MONTHLY;COUNT=5', $startDate, $timezone);
$transformer = new \Recurr\RecurrenceRuleTransformer($rule);

print_r($transformer->getComputedArray());
```


Contribute
----------

Recurr is still in beta, and is most likely not 100% free of bugs.
Feel free to comment or make pull requests. Please include tests with PRs.


License
-------

Recurr is licensed under the MIT License. See the LICENSE file for details.
