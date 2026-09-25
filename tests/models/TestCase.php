<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use Nette\Database\Explorer;
use Nette\Database\Helpers;
use Nette\DI\Container;
use Nette\Utils\Strings;
use Tester\TestCase as TesterTestCase;


abstract class TestCase extends TesterTestCase
{
	public function __construct
	(
		protected Container $Container,
		protected Explorer $Explorer
	)
	{
	}


	public function setUp(): void
	{
		$basicSql = join(DIRECTORY_SEPARATOR, [__DIR__, 'sql', 'basic.sql']);

		if(file_exists($basicSql))
		{
			Helpers::loadFromFile($this->Explorer->getConnection(), $basicSql);
		}

		$reflection = new \ReflectionObject($this);
		$fileName = $reflection->getFileName();

		if($fileName === false)
		{
			return;
		}

		$filePath = dirname($fileName) . '/sql/' . Strings::firstLower($reflection->getShortName()) . '.sql';

		if(file_exists($filePath))
		{
			Helpers::loadFromFile($this->Explorer->getConnection(), $filePath);
		}
	}
}
