<?php

declare(strict_types = 1);

namespace ModulIS\Datatype;

use Attribute;
use ModulIS\Exception\InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

/**
 * UUIDv7 - a new value is generated only for a non-nullable property (primary key) without value,
 * a nullable property (foreign key) keeps NULL
 */
#[Attribute]
class UuidDatatype extends Datatype
{
	public static function input(string $name, string $type, mixed $value): ?string
	{
		if($value !== null && !is_string($value))
		{
			throw new InvalidArgumentException('Invalid type for column "' . $name . '" - "string" expected, "' . get_debug_type($value) . '" given.');
		}

		return $value;
	}


	public static function output(string $type, mixed $value): ?string
	{
		if($value !== null && !is_string($value))
		{
			throw new InvalidArgumentException('Invalid value of type "' . get_debug_type($value) . '" for "' . $type . '" - "string" expected.');
		}

		return $value;
	}


	public static function generateDefault(string $type): string
	{
		return Uuid::v7()->toRfc4122();
	}
}
