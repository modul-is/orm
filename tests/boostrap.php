<?php

require __DIR__ . '/../vendor/autoload.php';

\Tester\Environment::setup();

date_default_timezone_set('UTC');

$path = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'temp']);

if(!file_exists($path))
{
    mkdir($path);
}

\Tester\Environment::lock('core', $path);
