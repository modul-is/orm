<?php

declare(strict_types=1);

ini_set('serialize_precision', '-1');
ini_set('precision', '-1');

require __DIR__ . '/../vendor/autoload.php';

$tempDir = __DIR__ . DIRECTORY_SEPARATOR . 'temp';

if(!is_dir($tempDir))
{
	mkdir($tempDir, recursive: true);
}

$cache = new \Nette\Caching\Cache(new \Nette\Caching\Storages\FileStorage($tempDir));
$cache->clean([$cache::All => true]);

$debug = false;

$configurator = new \Nette\Bootstrap\Configurator;
$configurator->setDebugMode($debug);

if($debug)
{
	$configurator->enableTracy(__DIR__ . DIRECTORY_SEPARATOR . 'log');
}

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory($tempDir);

$configurator->createRobotLoader()
	->addDirectory(__DIR__ . DIRECTORY_SEPARATOR . 'cases')
	->addDirectory(__DIR__ . DIRECTORY_SEPARATOR . 'models')
	->register();

$configurator->addConfig(join(DIRECTORY_SEPARATOR, [__DIR__, 'config', 'test.neon']));

\Tester\Environment::setup();
\Tester\Environment::lock('core', $tempDir);

return $configurator->createContainer();