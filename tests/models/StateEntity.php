<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Attribute\ReadonlyProperty;
use ModulIS\Datatype\UuidDatatype;
use ModulIS\Entity;


class StateEntity extends Entity
{
	#[ReadonlyProperty]
	#[UuidDatatype]
	public string $uuid;

	public string $name;

	public string $short;
}
