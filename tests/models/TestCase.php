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


	public function setUp()
	{
		$basicSql = join(DIRECTORY_SEPARATOR, [__DIR__, 'sql', 'basic.sql']);

		if(file_exists($basicSql))
		{
			Helpers::loadFromFile($this->Explorer->getConnection(), $basicSql);
		}

		$reflection = new \ReflectionObject($this);

		$parentDirectory = substr($reflection->getFileName(), 0, strrpos($reflection->getFileName(), DIRECTORY_SEPARATOR));
		$filePath = $parentDirectory . '/sql/' . Strings::firstLower($reflection->getShortName()) . '.sql';

		if(file_exists($filePath))
		{
			Helpers::loadFromFile($this->Explorer->getConnection(), $filePath);
		}
	}
}
