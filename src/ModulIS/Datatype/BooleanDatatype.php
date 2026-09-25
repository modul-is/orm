<?php

declare(strict_types = 1);

namespace ModulIS\Datatype;

use Attribute;


#[Attribute]
class BooleanDatatype extends Datatype
{
	public static function input(string $name, string $type, mixed $value): int
	{
		if(is_bool($value))
		{
			return (int) $value;
		}
		else
		{
			throw new \ModulIS\Exception\InvalidArgumentException('Invalid type for column "' . $name . '" - "bool" expected, "' . get_debug_type($value) . '" given.');
		}
	}


	public static function output(string $type, mixed $value): bool
	{
		return (bool) $value;
	}
}
