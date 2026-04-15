<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Repository;


class StateRepository extends Repository
{
	protected string $table = 'state';

	protected string $entity = StateEntity::class;


	public function getByID(int|string $id): ?StateEntity
	{
		return parent::getByID($id);
	}


	public function getBy(array $criteria): ?StateEntity
	{
		return parent::getBy($criteria);
	}
}
