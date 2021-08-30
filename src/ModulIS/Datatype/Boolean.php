<?php
declare(strict_types=1);

namespace ModulIS\Datatype;

class Boolean extends Datatype
{
	public static function input($value): int
	{
		if(is_bool($value))
		{
			return (int) $value;
		}
		else
		{
			throw new \ModulIS\Exception\InvalidArgumentException("Invalid type - 'bool' expected, '" . get_debug_type($value) . "' given.");
		}
	}


	public static function output($value): bool
	{
		return (bool) $value;
	}
}