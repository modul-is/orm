<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;

/**
 * @testCase
 */
class DatatypeCaseTest extends \Tester\TestCase
{
	protected $Service;


	public function setUp()
	{
		$this->Service = new Service;
		$this->Service->cache->clean([Nette\Caching\Cache::ALL]);
	}


	public function testIntToInt()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->weight = 1;

		Assert::same(1, $animalEntity->weight);
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testIntToFloat()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->price = 1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testIntToString()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testIntToArray()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->parameters = 1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testIntToBool()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->vaccinated = 1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testIntToDatetime()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->birth = 1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testFloatToInt()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->weight = 1.1;
	}


	public function testFloatToFloat()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->price = 1.1;

		Assert::same(1.1, $animalEntity->price);
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testFloatToString()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 1.1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testFloatToArray()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->parameters = 1.1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testFloatToBool()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->vaccinated = 1.1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testFloatToDatetime()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->birth = 1.1;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testStringToInt()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->weight = '1';
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testStringToFloat()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->price = '1';
	}


	public function testStringToString()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = '1';

		Assert::same('1', $animalEntity->name);
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testStringToArray()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->parameters = '1';
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testStringToBool()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->vaccinated = '1';
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testStringToDatetime()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->birth = '1';
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testArrayToInt()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->weight = [];
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testArrayToFloat()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->price = [];
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testArrayToString()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = [];
	}


	public function testArrayToArray()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->parameters = [];

		Assert::same([], $animalEntity->parameters);
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testArrayToBool()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->vaccinated = [];
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testArrayToDatetime()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->birth = [];
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testBoolToInt()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->weight = true;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testBoolToFloat()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->price = true;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testBoolToString()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = true;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testBoolToArray()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->parameters = true;
	}


	public function testBoolToBool()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->vaccinated = true;

		Assert::same(true, $animalEntity->vaccinated);
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testBoolToDatetime()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->birth = true;
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testDatetimeToInt()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->weight = new Nette\Utils\DateTime('2021-01-01');
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testDatetimeToFloat()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->price = new Nette\Utils\DateTime('2021-01-01');
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testDatetimeToString()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = new Nette\Utils\DateTime('2021-01-01');
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testDatetimeToArray()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->parameters = new Nette\Utils\DateTime('2021-01-01');
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testDatetimeToBool()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->vaccinated = new Nette\Utils\DateTime('2021-01-01');
	}


	public function testDatetimeToDatetime()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->birth = new Nette\Utils\DateTime('2021-01-01');

		Assert::same((new Nette\Utils\DateTime('2021-01-01'))->format('Y-m-d'), $animalEntity->birth->format('Y-m-d'));
	}


	public function testNullToNullable()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->death = null;

		Assert::same(null, $animalEntity->death);
	}


	/**
	 * @throws \ModulIS\Exception\InvalidArgumentException
	 */
	public function testNullToNotNullable()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->birth = null;
	}
}

$testcase = new DatatypeCaseTest;
$testcase->run();
