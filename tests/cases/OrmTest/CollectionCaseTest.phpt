<?php

declare(strict_types=1);

namespace ModulIS\Orm;

$testerContainer = require __DIR__ . '/../../Bootstrap.php';

use Tester\Assert;

class CollectionCaseTest extends TestCase
{
	/**
	 * Save collection to database
	 */
	public function testSaveCollection()
	{
		$list = [];

		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Gorilla';
		$animalEntity->weight = 350;
		$animalEntity->birth = new \Nette\Utils\DateTime('1998-10-01 12:00:00');
		$animalEntity->parameters = ['color' => 'black', 'ears' => 1, 'eyes' => 2];

		$list[] = $animalEntity;

		$animalEntity2 = new AnimalEntity;
		$animalEntity2->name = 'Giraffe';
		$animalEntity2->weight = 600;
		$animalEntity2->birth = new \Nette\Utils\DateTime('1992-03-01 12:00:00');
		$animalEntity2->parameters = ['color' => 'yellow', 'ears' => 2, 'eyes' => 2];

		$list[] = $animalEntity2;

		$animalRepository = $this->Container->getByType(AnimalRepository::class);
		$animalRepository->saveCollection($list);

		$collection = $animalRepository->findBy([]);

		Assert::same(2, $collection->count());

		$animalRepository->removeCollection($collection);

		$deleted = $animalRepository->findBy([]);

		Assert::same(0, $deleted->count());
	}
}

$testerContainer->createInstance(CollectionCaseTest::class)->run();
