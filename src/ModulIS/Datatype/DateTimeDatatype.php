<?php

declare(strict_types = 1);

namespace ModulIS\Datatype;

use Attribute;
use ModulIS\Exception\InvalidArgumentException;
use Nette\Utils\DateTime;


#[Attribute]
class DateTimeDatatype extends Datatype
{
	public static function input(string $name, string $type, $value): ?string
	{
		if($value instanceof DateTime)
		{
			$value = $value->__toString();
		}
		elseif($value === null)
		{
			$value = null;
		}
		elseif(!is_string($value))
		{
			throw new InvalidArgumentException('Invalid type for column "' . $name . '" - Instance of "Nette\Utils\DateTime" expected, "' . get_debug_type($value) . '" given.');
		}

		return $value;
	}


	/**
	 * @throws \Exception
	 */
	public static function output(string $type, $value): ?DateTime
	{
		return $value === null ? null : DateTime::from($value);
	}
}
