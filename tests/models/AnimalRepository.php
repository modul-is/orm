<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Repository;


class AnimalRepository extends Repository
{
	protected string $table = 'animal';

	protected string $entity = AnimalEntity::class;


	public function getByID(int|string $id): ?AnimalEntity
	{
		return parent::getByID($id);
	}


	public function getBy(array $criteria): ?AnimalEntity
	{
		return parent::getBy($criteria);
	}
}
