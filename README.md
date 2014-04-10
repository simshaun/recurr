# Recurr [![Build Status](https://travis-ci.org/simshaun/recurr.png)](https://travis-ci.org/simshaun/recurr.png)

Recurr is a PHP library for working with recurrence rules ([RRULE](http://tools.ietf.org/html/rfc2445)) and converting them in to DateTime objects.

Recurr was developed as a precursor for a calendar with recurring events, and is heavily inspired by [rrule.js](https://github.com/jkbr/rrule).

Installation
------------

Recurr is hosted on [packagist](http://packagist.org), meaning you can install it with [Composer](http://getcomposer.org/).

1. Create a composer.json file

    ```json
    {
        "require": {
            "simshaun/recurr": "dev-master"
        }
    }
    ```
   *We recommend using a stable version instead of dev-master.*

2. Install composer and run it

    ```sh
    wget http://getcomposer.org/composer.phar
    php composer.phar install
    ```

3. (Optional) Autoload Recurr

    ```php
    require 'vendor/autoload.php';
    ```


RRULE to DateTime objects
-----------

```php
$timezone    = 'America/New_York';
$startDate   = new \DateTime('2013-06-12 20:00:00', new \DateTimeZone($timezone));
$endDate     = new \DateTime('2013-06-14 20:00:00', new \DateTimeZone($timezone)); // Optional
$rule        = new \Recurr\Rule('FREQ=MONTHLY;COUNT=5', $startDate, $endDate, $timezone);
$transformer = new \Recurr\Transformer\ArrayTransformer();

print_r($transformer->transform($rule));
```

1. `$transformer->transform(...)` returns an array of `Recurrence` objects.
2. Each `Recurrence` has `getStart()` and `getEnd()` methods, each returning a `\DateTime` object.
3. If the transformed `Rule` lacks an end date, `getEnd()` will return a `\DateTime` object equal to that of `getStart()`.

RRULE to Text
--------------------------

Recurr supports transforming some recurrence rules in to human readable text.
This feature is still in beta and only supports yearly, monthly, weekly, and daily frequencies. It is not yet localized and only supports English.

```php
$rule = new Rule('FREQ=YEARLY;INTERVAL=2;COUNT=3;', new \DateTime());

$textTransformer = new TextTransformer();
echo $textTransformer->transform($rule);
```


Warnings
---------------

- Monthly recurring rules: **If your start date is on the 29th, 30th, or 31st, Recurr will skip the months that have less than that number of days.** This behavior is configurable:

```php
$timezone    = 'America/New_York';
$startDate   = new \DateTime('2013-01-31 20:00:00', new \DateTimeZone($timezone));
$rule        = new \Recurr\Rule('FREQ=MONTHLY;COUNT=5', $startDate, null, $timezone);
$transformer = new \Recurr\Transformer\ArrayTransformer();

$transformerConfig = new \Recurr\Transformer\ArrayTransformerConfig();
$transformerConfig->enableLastDayOfMonthFix();
$transformer->setConfig($transformerConfig);

print_r($transformer->transform($rule));

/* Recurrences:
 * 2013-01-31
 * 2013-02-28
 * 2013-03-31
 * 2013-04-30
 * 2013-05-31
 */
```


Contribute
----------

Recurr is still in beta, and is most likely not 100% free of bugs.
Feel free to comment or make pull requests. Please include tests with PRs.


License
-------

Recurr is licensed under the MIT License. See the LICENSE file for details.
