<?php

declare(strict_types=1);

namespace ModulIS\Orm\Tests;

$testerContainer = require __DIR__ . '/../../Bootstrap.php';


use ModulIS\Orm\StateRepository;
use ModulIS\Orm\TestCase;
use ModulIS\Orm\StateEntity;
use ModulIS\Orm\ZooEntity;
use Tester\Assert;


class UuidDatatypeTest extends TestCase
{
	public function testUuidGeneration(): void
	{
		$stateEntity = new StateEntity;

		$stateEntity->name = 'Czech Republic';
		$stateEntity->short = 'CZ';

		Assert::type('string', $stateEntity->uuid);
		Assert::match('~^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$~', $stateEntity->uuid);
		
		$stateRepository = $this->Container->getByType(StateRepository::class);

		$stateRepository->save($stateEntity);
	}


	public function testUuidJoin(): void
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

		Assert::true($stateEntity instanceof StateEntity);
		Assert::same($stateUuid, $stateEntity->uuid);
		Assert::same('Slovakia', $stateEntity->name);
	}


	/**
	 * The primary key is generated before the INSERT even when nobody has read it
	 */
	public function testUuidGeneratedOnPersist(): void
	{
		$stateEntity = new StateEntity;
		$stateEntity->name = 'Austria';
		$stateEntity->short = 'AT';

		$stateRepository = $this->Container->getByType(StateRepository::class);
		$stateRepository->save($stateEntity);

		Assert::match('~^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$~', $stateEntity->uuid);

		$row = $this->Explorer->table('state')->where('short', 'AT')->fetch();

		Assert::same($stateEntity->uuid, $row?->uuid);
	}


	/**
	 * Generated value is stable - a repeated read returns the same UUID that gets persisted
	 */
	public function testUuidStableBeforePersist(): void
	{
		$stateEntity = new StateEntity;
		$stateEntity->name = 'Poland';
		$stateEntity->short = 'PL';

		$uuid = $stateEntity->uuid;

		Assert::same($uuid, $stateEntity->uuid);

		$this->Container->getByType(StateRepository::class)->save($stateEntity);

		Assert::same($uuid, $stateEntity->uuid);
		Assert::same(1, $this->Explorer->table('state')->where('uuid', $uuid)->count('*'));
	}


	/**
	 * A nullable UUID (foreign key) is never generated - NULL stays NULL
	 */
	public function testNullableUuidStaysNull(): void
	{
		$zooEntity = new ZooEntity;
		$zooEntity->name = 'Prague Zoo';
		$zooEntity->motto = null;
		$zooEntity->state_uuid = null;

		Assert::null($zooEntity->state_uuid);

		$this->Explorer->table('zoo')->insert($zooEntity->toArray(['id']));

		$zooRow = $this->Explorer->table('zoo')->where('name', 'Prague Zoo')->fetch();
		$zooEntity = new ZooEntity($zooRow);

		Assert::null($zooEntity->state_uuid);
		Assert::null($zooEntity->getState());
	}
}

$testerContainer->createInstance(UuidDatatypeTest::class)->run();
