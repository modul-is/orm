<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Attribute\ReadonlyProperty;
use ModulIS\Datatype\UuidDatatype;
use ModulIS\Entity;
use Symfony\Component\Uid\UuidV7;
use Symfony\Polyfill\Uuid\Uuid;


class StateEntity extends Entity
{
	#[ReadonlyProperty]
	#[UuidDatatype]
	public UuidV7 $uuid;

	public string $name;

	public string $short;
}
