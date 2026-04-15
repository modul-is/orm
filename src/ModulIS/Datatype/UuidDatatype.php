<?php

declare(strict_types = 1);

namespace ModulIS\Datatype;

use Attribute;
use ModulIS\Exception\InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

#[Attribute]
class UuidDatatype extends Datatype
{
	public static function input(string $name, string $type, $value): string
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


	public static function output(string $type, $value): string
	{
		return $value === null ? self::generateUuid() : (string) $value;
	}


	private static function generateUuid(): string
	{
		return Uuid::v7()->toRfc4122();
	}
}
