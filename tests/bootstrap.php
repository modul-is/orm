<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/ModulIS/Entity.php';
require __DIR__ . '/../src/ModulIS/Repository.php';

require __DIR__ . '/models/AnimalEntity.php';
require __DIR__ . '/models/ZooEntity.php';
require __DIR__ . '/models/AnimalRepository.php';
require __DIR__ . '/models/Service.php';

Tester\Environment::setup();

date_default_timezone_set('UTC');

$path = join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'temp']);

if(!file_exists($path))
{
	mkdir($path);
}

Tester\Environment::lock('core', $path);
