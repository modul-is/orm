<?php

declare(strict_types=1);

class Service
{
	public Nette\Database\Explorer $database;

	public Nette\Caching\Storages\FileStorage $cache;


	public function __construct()
	{
		$connection = new Nette\Database\Connection('mysql:host=127.0.0.1;dbname=test;port=3306', 'root', 'test');

		Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/sql/db.sql');
		$structure = new Nette\Database\Structure($connection, new Nette\Caching\Storages\FileStorage(__DIR__ . '/../../temp/'));
		$conventions = new Nette\Database\Conventions\DiscoveredConventions($structure);

		$this->database = new Nette\Database\Explorer($connection, $structure, $conventions, new Nette\Caching\Storages\FileStorage(__DIR__ . '/../../temp/'));

		$this->cache = new Nette\Caching\Storages\FileStorage(__DIR__ . '/../../temp');
	}
}
