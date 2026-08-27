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


	public function getState(): ?StateEntity
	{
		$record = $this->record->ref('state', 'state_uuid');

		return $record === null ? null : new StateEntity($record);
	}
}
