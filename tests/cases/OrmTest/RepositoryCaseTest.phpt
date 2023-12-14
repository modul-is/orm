<?php

declare(strict_types=1);

namespace ModulIS\Orm;

$testerContainer = require __DIR__ . '/../../Bootstrap.php';

use Tester\Assert;

class RepositoryCaseTest extends TestCase
{
	/**
	 * Save entity to database
	 */
	public function testSaveEntity()
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Gorilla';
		$animalEntity->weight = 350;
		$animalEntity->birth = new \Nette\Utils\DateTime('1998-10-01 12:00:00');
		$animalEntity->parameters = ['color' => 'black', 'ears' => 1, 'eyes' => 2];

		$animalEntity2 = new AnimalEntity;
		$animalEntity2->name = 'Giraffe';
		$animalEntity2->weight = 600;
		$animalEntity2->birth = new \Nette\Utils\DateTime('1992-03-01 12:00:00');
		$animalEntity2->parameters = ['color' => 'yellow', 'ears' => 2, 'eyes' => 2];

		$repository = $this->Container->getByType(AnimalRepository::class);
		$result = $repository->save($animalEntity);

		Assert::true($result);

		$result2 = $repository->save($animalEntity2);

		Assert::true($result2);

		$collection = $repository->findBy([]);

		Assert::true($collection instanceof \ModulIS\EntityCollection);
		Assert::same(2, $collection->count());

		/**
		 * TEST: Load entity from DB by criteria
		 */
		$entity = $repository->getBy(['name' => 'Giraffe']);

		Assert::true($entity instanceof \ModulIS\Entity);
		Assert::same('Giraffe', $entity->name);

		/**
		 * TEST: Update entity
		 */
		$entity->weight = 800;

		$repository->save($entity);

		/* @var $loadedEntity AnimalEntity */
		$loadedEntity = $repository->getBy(['id' => $entity->id]);

		Assert::same(800, $loadedEntity->weight);

		/**
		 * TEST: Fetch pairs
		 */
		$pairs = $repository->fetchPairs('id', 'name', [], 'id DESC');

		Assert::type('array', $pairs);

		Assert::same('Giraffe', $pairs[2]);

		/**
		 * Test fetch pairs
		 */
		$array = $repository->fetchPairs('id', 'name');

		Assert::same([1 => 'Gorilla', 2 => 'Giraffe'], $array);

		/**
		 * TEST: Remove entity from DB
		 */
		$remove = $repository->delete($loadedEntity);

		Assert::true($remove);

		$deletedEntity = $repository->getBy(['id' => $loadedEntity->id]);

		Assert::null($deletedEntity);

		/**
		 * TEST: Remove entity by ID
		 */
		$deletedByIdEntity = $repository->deleteByID(1);

		Assert::true($deletedByIdEntity);

		$deletedByIdEntity = $repository->getByID(1);

		Assert::null($deletedByIdEntity);
	}
}

$testerContainer->createInstance(RepositoryCaseTest::class)->run();
