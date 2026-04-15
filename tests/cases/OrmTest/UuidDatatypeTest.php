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
		
		$stateRepository = $this->Container->getServiceType(StateRepository::class);

		$stateRepository->save($stateEntity);
	}
}

$testerContainer->createInstance(UuidDatatypeTest::class)->run();
