<?php

declare(strict_types=1);

namespace ModulIS\Orm;

abstract class TestCase extends \Tester\TestCase
{
	public function __construct
	(
		protected \Nette\DI\Container $Container,
		protected \Nette\Database\Explorer $Explorer
	)
	{
	}


	public function setUp()
	{
		$basicSql = join(DIRECTORY_SEPARATOR, [__DIR__, 'sql', 'basic.sql']);

		if(file_exists($basicSql))
		{
			\Nette\Database\Helpers::loadFromFile($this->Explorer->getConnection(), $basicSql);
		}

		$reflection = new \ReflectionObject($this);

		$parentDirectory = substr($reflection->getFileName(), 0, strrpos($reflection->getFileName(), DIRECTORY_SEPARATOR));
		$filePath = $parentDirectory . '/sql/' . \Nette\Utils\Strings::firstLower($reflection->getShortName()) . '.sql';

		if(file_exists($filePath))
		{
			\Nette\Database\Helpers::loadFromFile($this->Explorer->getConnection(), $filePath);
		}
	}
}
