<?php

declare(strict_types=1);

namespace ModulIS\Datatype;

use Attribute;
use Nette\Utils\Json;


#[Attribute]
class JsonDatatype extends Datatype
{
	public static function input(?string $name, string $type, $value): ?string
	{
		if(is_array($value))
		{
			$value = Json::encode($value);
		}
		elseif($value !== null)
		{
			throw new \ModulIS\Exception\InvalidArgumentException("Invalid type for column '{$name}' - 'array' expected, '" . get_debug_type($value) . "' given.");
		}

		return $value;
	}


	public static function output(string $type, $value): array
	{
		return Json::decode($value, Json::FORCE_ARRAY);
	}
}
