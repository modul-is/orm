<?php

declare(strict_types=1);

namespace ModulIS\Orm;

$testerContainer = require __DIR__ . '/../../Bootstrap.php';

use ModulIS\Entity;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;
use Tester\Assert;

class EntityCaseTest extends TestCase
{
	/**
	 * Set entity property to null
	 */
	public function testEntitySetNull(): void
	{
		$zooEntity = new ZooEntity;
		$zooEntity->name = 'Zoo Pilsen';
		$zooEntity->motto = null;

		Assert::null($zooEntity->motto);
	}


	/**
	 * Entity to Array
	 */
	public function testEntityToArray(): void
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Kangaroo';
		$animalEntity->weight = 15;
		$animalEntity->birth = new DateTime('2015-01-01 12:00:00');
		$animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];
		$animalEntity->death = null;
		$animalEntity->vaccinated = true;
		$animalEntity->height = 50;
		$animalEntity->price = 999.90;
		$animalEntity->type = AnimalEnum::Mammal;

		$array = $animalEntity->toArray(['id']);

		Assert::same('Kangaroo', $array['name']);
		Assert::same(['color' => 'brown', 'ears' => 2, 'eyes' => 1], $array['parameters']);
		Assert::false(array_key_exists('id', $array));
	}


	public function testEntityToArrayEdgeCase(): void
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = '';
		$animalEntity->weight = 0;
		$animalEntity->birth = new DateTime('2015-01-01 12:00:00');
		$animalEntity->parameters = [];
		$animalEntity->death = null;
		$animalEntity->vaccinated = true;
		$animalEntity->height = 0;
		$animalEntity->price = 0.0;
		$animalEntity->type = AnimalEnum::Fish;

		$array = $animalEntity->toArray(['id']);

		Assert::same('', $array['name']);
		Assert::same(0, $array['weight']);
		Assert::same(0.0, $array['price']);
		Assert::same([], $array['parameters']);
	}


	public function testEntityToArrayNullProperty(): void
	{
		$zooEntity = new ZooEntity;
		$zooEntity->name = 'Lion';

		$array = $zooEntity->toArray(['id']);

		Assert::null($array['motto']);
	}


	/**
	 * Entity filled from Array
	 */
	public function testEntityFromArray(): void
	{
		$array = [
			'name' => 'Kangaroo',
			'weight' => 15,
			'birth' => new DateTime,
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




	public function testEntitySaveToDatabaseDriver(): void
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Kangaroo';
		$animalEntity->weight = 15;
		$animalEntity->birth = new DateTime('2015-01-01 12:00:00');
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
		Assert::true($loadedEntity instanceof Entity);

		/**
		 * TEST: save & load it back like array via JSON
		 */
		Assert::same(['color' => 'brown', 'ears' => 2, 'eyes' => 1], $loadedEntity->parameters);

		/**
		 * TEST: save & load \Nette\Utils\DateTime with the right value
		 */
		Assert::same($loadedEntity->birth->format('Y'), '2015');
		Assert::same($loadedEntity->birth->format('m-d'), '01-01');
		Assert::same($loadedEntity->birth->format('H:i:s'), '12:00:00');
	}


	public function testEntitySaveToDatabaseWithoutDriver(): void
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Kangaroo';
		$animalEntity->weight = 15;
		$animalEntity->birth = new DateTime('2015-01-01 12:00:00');
		$animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];
		$animalEntity->death = null;
		$animalEntity->vaccinated = true;
		$animalEntity->height = 50;

		$databaseWithoutDriver = $this->Container->getByName('database.withoutdriver.context');

		Assert::true($databaseWithoutDriver instanceof Explorer);

		$repository = new AnimalRepository($databaseWithoutDriver);

		$repository->save($animalEntity);

		$loadedEntity = $repository->getBy(['name' => 'Kangaroo']);

		/**
		 * TEST: save entity to database
		 */
		Assert::true($loadedEntity instanceof Entity);

		/**
		 * TEST: save & load it back like array via JSON
		 */
		Assert::same(['color' => 'brown', 'ears' => 2, 'eyes' => 1], $loadedEntity->parameters);

		/**
		 * TEST: save & load \Nette\Utils\DateTime with the right value
		 */
		Assert::same($loadedEntity->birth->format('Y'), '2015');
		Assert::same($loadedEntity->birth->format('m-d'), '01-01');
		Assert::same($loadedEntity->birth->format('H:i:s'), '12:00:00');
	}


	/**
	 * Isset empty property
	 */
	public function testIssetEmptyProperty(): void
	{
		$animalEntity = new AnimalEntity;

		Assert::false($animalEntity->__isset('name'));
	}
}

$testerContainer->createInstance(EntityCaseTest::class)->run();
