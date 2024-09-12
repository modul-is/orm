<?php

declare(strict_types=1);

namespace ModulIS\Datatype;

use Attribute;
use BackedEnum;
use ModulIS\Exception\InvalidArgumentException;


#[Attribute]
class EnumDatatype extends Datatype
{
	public static function input(string $name, string $type, $value): mixed
	{
		if(is_string($value))
		{
			$value = $type::tryFrom($value);

			if($value === null)
			{
				throw new InvalidArgumentException("Invalid value for column '" . $name . "' - Value '" . $value . "' is not part of enum '" . $type . "'");
			}
		}

		return $value;
	}


	public static function output(string $type, $value): BackedEnum
	{
		if(is_string($value))
		{
			$value = $type::tryFrom($value);
		}

		return $value;
	}
}
