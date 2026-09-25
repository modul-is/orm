<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Repository;


/**
 * @extends Repository<AnimalEntity>
 */
class AnimalRepository extends Repository
{
	protected string $table = 'animal';

	protected string $entity = AnimalEntity::class;
}
