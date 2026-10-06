<?php

declare(strict_types=1);

namespace ModulIS\Orm;

$testerContainer = require __DIR__ . '/../../Bootstrap.php';

use ModulIS\Entity;
use ModulIS\Exception\EntityNotFoundException;
use Nette\Utils\DateTime;
use Tester\Assert;

class RepositoryCaseTest extends TestCase
{
	/**
	 * Save entity to database
	 */
	public function testSaveEntity(): void
	{
		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Gorilla';
		$animalEntity->weight = 350;
		$animalEntity->birth = new DateTime('1998-10-01 12:00:00');
		$animalEntity->parameters = ['color' => 'black', 'ears' => 1, 'eyes' => 2];

		$animalEntity2 = new AnimalEntity;
		$animalEntity2->name = 'Giraffe';
		$animalEntity2->weight = 600;
		$animalEntity2->birth = new DateTime('1992-03-01 12:00:00');
		$animalEntity2->parameters = ['color' => 'yellow', 'ears' => 2, 'eyes' => 2];

		$repository = $this->Container->getByType(AnimalRepository::class);
		$result = $repository->save($animalEntity);

		Assert::true($result);

		$result2 = $repository->save($animalEntity2);

		Assert::true($result2);

		$collection = $repository->findBy([]);

		Assert::same(2, $collection->count());

		/**
		 * TEST: Load entity from DB by criteria
		 */
		$entity = $repository->getBy(['name' => 'Giraffe']);

		Assert::true($entity instanceof Entity);
		Assert::same('Giraffe', $entity->name);

		/**
		 * TEST: Update entity
		 */
		$entity->weight = 800;

		$repository->save($entity);

		$loadedEntity = $repository->getBy(['id' => $entity->id]);

		Assert::true($loadedEntity instanceof Entity);
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

		/**
		 * TEST: OrFail variants
		 */
		Assert::exception(fn() => $repository->getByIDOrFail(1), EntityNotFoundException::class);
		Assert::exception(fn() => $repository->getByOrFail(['name' => 'Gorilla']), EntityNotFoundException::class);

		$animalEntity3 = new AnimalEntity;
		$animalEntity3->name = 'Zebra';
		$animalEntity3->weight = 300;
		$animalEntity3->birth = new DateTime('2001-05-01 12:00:00');
		$animalEntity3->parameters = [];
		$repository->save($animalEntity3);

		Assert::same('Zebra', $repository->getByIDOrFail($animalEntity3->id)->name);
		Assert::same($animalEntity3->id, $repository->getByOrFail(['name' => 'Zebra'])->id);
	}
}

$testerContainer->createInstance(RepositoryCaseTest::class)->run();
