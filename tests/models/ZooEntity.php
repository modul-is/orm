<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Entity;


class ZooEntity extends Entity
{
	public int $id;

	public string $name;

	public ?string $motto;
}
