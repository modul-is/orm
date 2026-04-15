<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Entity;


class ZooEntity extends Entity
{
	public int $id;

	public string $name;

	public ?string $motto;

	public ?string $state_uuid;


	public function getState(): StateEntity
	{
		return $this->record->ref(StateEntity::class, 'state_uuid');
	}
}
