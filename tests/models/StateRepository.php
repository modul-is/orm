<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Repository;


/**
 * @extends Repository<StateEntity>
 */
class StateRepository extends Repository
{
	protected string $table = 'state';

	protected string $entity = StateEntity::class;
}
