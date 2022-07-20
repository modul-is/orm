<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;

/**
 * @testCase
 */
class CollectionCaseTest extends \Tester\TestCase
{
	protected $Service;


	public function setUp()
	{
		$this->Service = new Service;
		$this->Service->cache->clean([Nette\Caching\Cache::ALL]);
	}


	/**
	 * Entity to Array
	 */
	public function testSaveEntityToDatabase()
	{
		$list = [];

		$animalEntity = new AnimalEntity;
		$animalEntity->name = 'Gorilla';
		$animalEntity->weight = 350;
		$animalEntity->birth = new Nette\Utils\DateTime('1998-10-01 12:00:00');
		$animalEntity->parameters = ['color' => 'black', 'ears' => 1, 'eyes' => 2];

		$list[] = $animalEntity;

		$animalEntity2 = new AnimalEntity;
		$animalEntity2->name = 'Giraffe';
		$animalEntity2->weight = 600;
		$animalEntity2->birth = new Nette\Utils\DateTime('1992-03-01 12:00:00');
		$animalEntity2->parameters = ['color' => 'yellow', 'ears' => 2, 'eyes' => 2];

		$list[] = $animalEntity2;

		$animalRepository = new AnimalRepository($this->Service->database);
		$animalRepository->saveCollection($list);

		$collection = $animalRepository->findBy([]);

		Assert::same(2, $collection->count());

		$animalRepository->removeCollection($collection);

		$deleted = $animalRepository->findBy([]);

		Assert::same(0, $deleted->count());
	}
}

$testCase = new CollectionCaseTest;
$testCase->run();
