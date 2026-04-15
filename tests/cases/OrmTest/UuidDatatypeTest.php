<?php

declare(strict_types=1);

namespace ModulIS\Orm\Tests;

$testerContainer = require __DIR__ . '/../../Bootstrap.php';


use ModulIS\Orm\StateRepository;
use ModulIS\Orm\TestCase;
use ModulIS\Orm\StateEntity;
use Tester\Assert;


class UuidDatatypeTest extends TestCase
{
	public function testUuidGeneration()
	{
		$stateEntity = new StateEntity;

		$stateEntity->name = 'Czech Republic';
		$stateEntity->short = 'CZ';

		Assert::type('string', $stateEntity->uuid);
		Assert::match('~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~', $stateEntity->uuid);
		
		$stateRepository = $this->Container->getByType(StateRepository::class);

		$stateRepository->save($stateEntity);
	}


	public function testUuidJoin()
	{
		$stateEntity = new StateEntity;
		$stateEntity->name = 'Slovakia';
		$stateEntity->short = 'SK';

		$stateUuid = $stateEntity->uuid;

		$stateRepository = $this->Container->getByType(StateRepository::class);
		$stateRepository->save($stateEntity);

		$zooEntity = new \ModulIS\Orm\ZooEntity;
		$zooEntity->name = 'Bratislava Zoo';
		$zooEntity->state_uuid = $stateUuid;

		$this->Explorer->table('zoo')->insert($zooEntity->toArray(['id']));

		$zooRow = $this->Explorer->table('zoo')->where('name', 'Bratislava Zoo')->fetch();
		$zooEntity = new \ModulIS\Orm\ZooEntity($zooRow);

		$stateEntity = $zooEntity->getState();

		Assert::type(StateEntity::class, $stateEntity);
		Assert::same($stateUuid, $stateEntity->uuid);
		Assert::same('Slovakia', $stateEntity->name);
	}
}

$testerContainer->createInstance(UuidDatatypeTest::class)->run();
