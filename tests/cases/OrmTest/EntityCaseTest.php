<?php

declare(strict_types=1);

namespace ModulIS\Orm;

$testerContainer = require __DIR__ . '/../../Bootstrap.php';

use Tester\Assert;

class EntityCaseTest extends TestCase
{
	/**
	 * Set entity property to null
	 */
	public function testEntitySetNull()
	{
		$zooEntity = new ZooEntity;
		$zooEntity->name = 'Zoo Pilsen';
		$zooEntity->motto = null;

		Assert::null($zooEntity->motto);
	}


	/**
	 * Entity to Array
	 */
	public function testEntityToArray()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Kangaroo';
		$animalEntity->weight = 15;
		$animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
		$animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];
		$animalEntity->death = null;
		$animalEntity->vaccinated = true;
		$animalEntity->height = 50;
		$animalEntity->price = 999.90;
		$animalEntity->type = AnimalEnum::MAMMAL;

		$array = $animalEntity->toArray(['id']);

		Assert::true(is_array($array));
	}


	public function testEntityToArrayEdgeCase()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = '';
		$animalEntity->weight = 0;
		$animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
		$animalEntity->parameters = [];
		$animalEntity->death = null;
		$animalEntity->vaccinated = true;
		$animalEntity->height = 0;
		$animalEntity->price = 0.0;
		$animalEntity->type = AnimalEnum::FISH;

		$array = $animalEntity->toArray(['id']);

		Assert::same('', $array['name']);
		Assert::same(0, $array['weight']);
		Assert::same(0.0, $array['price']);
		Assert::same([], $array['parameters']);
	}


	public function testEntityToArrayNullProperty()
	{
		$zooEntity = new ZooEntity;
		$zooEntity->name = 'Lion';

		$array = $zooEntity->toArray(['id']);

		Assert::null($array['motto']);
	}


	/**
	 * Entity filled from Array
	 */
	public function testEntityFromArray()
	{
		$array = [
			'name' => 'Kangaroo',
			'weight' => 15,
			'birth' => new \Nette\Utils\DateTime,
			'parameters' => [
				'color' => 'brown',
				'ears' => 2,
				'eyes' => 1
			],
			'death' => null,
			'height' => '50',
			'vaccinated' => true
		];

		$kangarooEntity = new AnimalEntity;
		$kangarooEntity->fillFromArray($array);

		Assert::same(15, $kangarooEntity->weight);

		/**
		 * TEST: bool to int conversion
		 */
		Assert::true($kangarooEntity->vaccinated);

		/**
		 * TEST: string to int conversion
		 */
		Assert::same(50, $kangarooEntity->height);

		/**
		 * TEST: Filling null values from array
		 */
		Assert::null($kangarooEntity->death);
	}


	/**
	 * Entity save to database
	 */
	public function testEntitySaveToDatabase()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Kangaroo';
		$animalEntity->weight = 15;
		$animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
		$animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];
		$animalEntity->death = null;
		$animalEntity->vaccinated = true;
		$animalEntity->height = 50;

		$repository = $this->Container->getByType(AnimalRepository::class);
		$repository->save($animalEntity);

		$loadedEntity = $repository->getBy(['name' => 'Kangaroo']);

		/**
		 * TEST: save entity to database
		 */
		Assert::true($loadedEntity instanceof \ModulIS\Entity);

		/**
		 * TEST: save & load it back like array via JSON
		 */
		Assert::same(['color' => 'brown', 'ears' => 2, 'eyes' => 1], $loadedEntity->parameters);

		/**
		 * TEST: save & load \Nette\Utils\DateTime
		 */
		Assert::true($loadedEntity->birth instanceof \Nette\Utils\DateTime);

		/**
		 * TEST: check right type of date
		 */
		Assert::same($loadedEntity->birth->format('Y'), '2015');
		Assert::same($loadedEntity->birth->format('m-d'), '01-01');
		Assert::same($loadedEntity->birth->format('H:i:s'), '12:00:00');
	}


	/**
	 * Isset empty property
	 */
	public function testIssetEmptyProperty()
	{
		$animalEntity = new AnimalEntity;

		Assert::false($animalEntity->__isset('name'));
	}
}

$testerContainer->createInstance(EntityCaseTest::class)->run();
