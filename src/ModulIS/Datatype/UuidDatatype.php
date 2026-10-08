<?php

declare(strict_types = 1);

namespace ModulIS\Datatype;

use Attribute;
use ModulIS\Exception\InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

#[Attribute]
class UuidDatatype extends Datatype
{
	public static function input(string $name, string $type, mixed $value): string
	{
		if($value === null)
		{
			return self::generateUuid();
		}
		elseif(!is_string($value))
		{
			throw new InvalidArgumentException('Invalid type for column "' . $name . '" - "string" expected, "' . get_debug_type($value) . '" given.');
		}

		return $value;
	}


	public static function output(string $type, mixed $value): string
	{
		if($value === null)
		{
			return self::generateUuid();
		}

		if(!is_string($value))
		{
			throw new InvalidArgumentException('Invalid value of type "' . get_debug_type($value) . '" for "' . $type . '" - "string" expected.');
		}

		return $value;
	}


	public static function generateDefault(string $type): string
	{
		return self::generateUuid();
	}


	private static function generateUuid(): string
	{
		return Uuid::v7()->toRfc4122();
	}
}
