<?php

declare(strict_types = 1);

namespace ModulIS\Datatype;

use Attribute;
use BackedEnum;
use ModulIS\Exception\InvalidArgumentException;


#[Attribute]
class EnumDatatype extends Datatype
{
	public static function input(string $name, string $type, mixed $value): mixed
	{
		if(is_string($value) || is_int($value))
		{
			self::checkEnum($type);

			$enum = $type::tryFrom($value);

			if($enum === null)
			{
				throw new InvalidArgumentException('Invalid value for column "' . $name . '" - Value "' . $value . '" is not part of enum "' . $type . '"');
			}

			$value = $enum;
		}

		return $value;
	}


	public static function output(string $type, mixed $value): ?BackedEnum
	{
		if(is_string($value) || is_int($value))
		{
			self::checkEnum($type);

			return $type::tryFrom($value);
		}

		return $value instanceof BackedEnum ? $value : null;
	}


	/**
	 * @phpstan-assert class-string<BackedEnum> $type
	 */
	private static function checkEnum(string $type): void
	{
		if(!is_subclass_of($type, BackedEnum::class))
		{
			throw new InvalidArgumentException('Type "' . $type . '" is not a backed enum.');
		}
	}
}
