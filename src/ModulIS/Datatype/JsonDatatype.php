<?php

declare(strict_types = 1);

namespace ModulIS\Datatype;

use Attribute;
use ModulIS\Exception\InvalidArgumentException;
use Nette\Utils\Json;


#[Attribute]
class JsonDatatype extends Datatype
{
	public static function input(?string $name, string $type, mixed $value): ?string
	{
		if(is_array($value))
		{
			$value = Json::encode($value);
		}
		elseif($value !== null)
		{
			throw new InvalidArgumentException('Invalid type for column "' . $name . '" - "array" expected, "' . get_debug_type($value) . '" given.');
		}

		return $value;
	}


	/**
	 * @return array<mixed>|null
	 */
	public static function output(string $type, mixed $value): ?array
	{
		if($value === null)
		{
			return null;
		}

		if(!is_string($value))
		{
			throw new InvalidArgumentException('Invalid value of type "' . get_debug_type($value) . '" for "' . $type . '" - "string" expected.');
		}

		$decoded = Json::decode($value, forceArrays: true);

		return is_array($decoded) ? $decoded : [$decoded];
	}
}
