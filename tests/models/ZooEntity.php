<?php

declare(strict_types=1);

namespace ModulIS\Orm;

class ZooEntity extends \ModulIS\Entity
{
	public int $id;

	public string $name;

	public ?string $motto;
}
