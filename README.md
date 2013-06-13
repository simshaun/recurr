# Recurr

Recurr is a PHP library for working with recurrence rules that results in
PHP \DateTime objects.

Installation
------------

Recurr is hosted on [packagist](http://packagist.org), meaning you can install
it with [Composer](http://getcomposer.org/).

Create a composer.json file

```json
{
    "require": {
        "simshaun/recurr": "1.*"
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
