<?php

declare(strict_types=1);

namespace ModulIS\Datatype;

class EnumDatatype extends Datatype
{
	public static function input(string $name, string $type, $value)
	{
		if(is_string($value))
		{
			$value = $type::tryFrom($value);

			if($value === null)
			{
				throw new \ModulIS\Exception\InvalidArgumentException("Invalid value for column '$name' - Value '$value' is not part of enum '" . $type . "'");
			}
		}

		return $value;
	}


	public static function output(string $type, $value): \BackedEnum
	{
		if(is_string($value))
		{
			$value = $type::tryFrom($value);
		}

		return $value;
	}
}
