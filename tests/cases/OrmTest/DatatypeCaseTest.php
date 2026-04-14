<?php

declare(strict_types=1);

namespace ModulIS\Orm;

$testerContainer = require __DIR__ . '/../../Bootstrap.php';

use ModulIS\Exception\InvalidArgumentException;
use Nette\Utils\DateTime;
use Tester\Assert;

class DatatypeCaseTest extends TestCase
{
	public function testIntToInt()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->weight = 1;

		Assert::same(1, $animalEntity->weight);
	}


	public function testIntToFloat()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->price = 1,
			InvalidArgumentException::class,
			'Invalid type for column "price" - "float" expected, "int" given.'
		);
	}


	public function testIntToString()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->name = 1,
			InvalidArgumentException::class,
			'Invalid type for column "name" - "string" expected, "int" given.'
		);
	}


	public function testIntToArray()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->parameters = 1,
			InvalidArgumentException::class,
			'Invalid type for column "parameters" - "array" expected, "int" given.'
		);
	}


	public function testIntToBool()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->vaccinated = 1,
			InvalidArgumentException::class,
			'Invalid type for column "vaccinated" - "bool" expected, "int" given.'
		);
	}


	public function testIntToDatetime()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->birth = 1,
			InvalidArgumentException::class,
			'Invalid type for column "birth" - Instance of "Nette\Utils\DateTime" expected, "int" given.'
		);
	}


	public function testFloatToInt()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->weight = 1.1,
			InvalidArgumentException::class,
			'Invalid type for column "weight" - "int" expected, "float" given.'
		);
	}


	public function testFloatToFloat()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->price = 1.1;

		Assert::same(1.1, $animalEntity->price);
	}


	public function testFloatToString()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->name = 1.1,
			InvalidArgumentException::class,
			'Invalid type for column "name" - "string" expected, "float" given.'
		);
	}


	public function testFloatToArray()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->parameters = 1.1,
			InvalidArgumentException::class,
			'Invalid type for column "parameters" - "array" expected, "float" given.'
		);
	}


	public function testFloatToBool()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->vaccinated = 1.1,
			InvalidArgumentException::class,
			'Invalid type for column "vaccinated" - "bool" expected, "float" given.'
		);
	}


	public function testFloatToDatetime()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->birth = 1.1,
			InvalidArgumentException::class,
			'Invalid type for column "birth" - Instance of "Nette\Utils\DateTime" expected, "float" given.'
		);
	}


	public function testStringToInt()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->weight = '1',
			InvalidArgumentException::class,
			'Invalid type for column "weight" - "int" expected, "string" given.'
		);
	}


	public function testStringToFloat()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->price = '1',
			InvalidArgumentException::class,
			'Invalid type for column "price" - "float" expected, "string" given.'
		);
	}


	public function testStringToString()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = '1';

		Assert::same('1', $animalEntity->name);
	}


	public function testStringToArray()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->parameters = '1',
			InvalidArgumentException::class,
			'Invalid type for column "parameters" - "array" expected, "string" given.'
		);
	}


	public function testStringToBool()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->vaccinated = '1',
			InvalidArgumentException::class,
			'Invalid type for column "vaccinated" - "bool" expected, "string" given.'
		);
	}


	public function testArrayToInt()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->weight = [],
			InvalidArgumentException::class,
			'Invalid type for column "weight" - "int" expected, "array" given.'
		);
	}


	public function testArrayToFloat()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->price = [],
			InvalidArgumentException::class,
			'Invalid type for column "price" - "float" expected, "array" given.'
		);
	}


	public function testArrayToString()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->name = [],
			InvalidArgumentException::class,
			'Invalid type for column "name" - "string" expected, "array" given.'
		);
	}


	public function testArrayToArray()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->parameters = [];

		Assert::same([], $animalEntity->parameters);
	}


	public function testArrayToBool()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->vaccinated = [],
			InvalidArgumentException::class,
			'Invalid type for column "vaccinated" - "bool" expected, "array" given.'
		);
	}


	public function testArrayToDatetime()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->birth = [],
			InvalidArgumentException::class,
			'Invalid type for column "birth" - Instance of "Nette\Utils\DateTime" expected, "array" given.'
		);
	}


	public function testBoolToInt()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->weight = true,
			InvalidArgumentException::class,
			'Invalid type for column "weight" - "int" expected, "bool" given.'
		);
	}


	public function testBoolToFloat()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->price = true,
			InvalidArgumentException::class,
			'Invalid type for column "price" - "float" expected, "bool" given.'
		);
	}


	public function testBoolToString()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->name = true,
			InvalidArgumentException::class,
			'Invalid type for column "name" - "string" expected, "bool" given.'
		);
	}


	public function testBoolToArray()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->parameters = true,
			InvalidArgumentException::class,
			'Invalid type for column "parameters" - "array" expected, "bool" given.'
		);
	}


	public function testBoolToBool()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->vaccinated = true;

		Assert::true($animalEntity->vaccinated);
	}


	public function testBoolToDatetime()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->birth = true,
			InvalidArgumentException::class,
			'Invalid type for column "birth" - Instance of "Nette\Utils\DateTime" expected, "bool" given.'
		);
	}


	public function testDatetimeToInt()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->weight = new DateTime('2021-01-01'),
			InvalidArgumentException::class,
			'Invalid type for column "weight" - "int" expected, "Nette\Utils\DateTime" given.'
		);
	}


	public function testDatetimeToFloat()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->price = new DateTime('2021-01-01'),
			InvalidArgumentException::class,
			'Invalid type for column "price" - "float" expected, "Nette\Utils\DateTime" given.'
		);
	}


	public function testDatetimeToString()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->name = new DateTime('2021-01-01'),
			InvalidArgumentException::class,
			'Invalid type for column "name" - "string" expected, "Nette\Utils\DateTime" given.'
		);
	}


	public function testDatetimeToArray()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->parameters = new DateTime('2021-01-01'),
			InvalidArgumentException::class,
			'Invalid type for column "parameters" - "array" expected, "Nette\Utils\DateTime" given.'
		);
	}


	public function testDatetimeToBool()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->vaccinated = new DateTime('2021-01-01'),
			InvalidArgumentException::class,
			'Invalid type for column "vaccinated" - "bool" expected, "Nette\Utils\DateTime" given.'
		);
	}


	public function testDatetimeToDatetime()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->birth = new DateTime('2021-01-01');

		Assert::same((new DateTime('2021-01-01'))->format('Y-m-d'), $animalEntity->birth->format('Y-m-d'));
	}


	public function testNullToNullable()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->death = null;

		Assert::null($animalEntity->death);
	}


	public function testNullToNotNullable()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->parameters = null,
			InvalidArgumentException::class,
			'Property "ModulIS\Orm\AnimalEntity::$parameters" cannot be null.'
		);
	}


	public function testEnumToEnum()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->type = AnimalEnum::Mammal;

		Assert::same(AnimalEnum::Mammal, $animalEntity->type);
	}


	public function testStringToEnum()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->type = 'fish';

		Assert::same(AnimalEnum::Fish, $animalEntity->type);
	}


	public function testWrongStringToEnum()
	{
		$animalEntity = new AnimalEntity;

		Assert::exception(
			fn() => $animalEntity->type = '',
			InvalidArgumentException::class,
			'Invalid value for column "type" - Value "" is not part of enum "ModulIS\Orm\AnimalEnum"'
		);
	}
}

$testerContainer->createInstance(DatatypeCaseTest::class)->run();
