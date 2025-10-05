<?php

use Composer\Autoload\ClassLoader;

require_once __DIR__.'/../vendor/autoload.php';

error_reporting(E_ALL);

$classLoader = new ClassLoader();
$classLoader->add('Test\\Recurr', __DIR__);
$classLoader->register(true);
