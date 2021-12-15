<?php
declare(strict_types=1);

namespace ModulIS\Datatype;

class Json extends Datatype
{
	public static function input($value): string
	{
		if(is_array($value))
		{
			$value = \Nette\Utils\Json::encode($value);
		}
		else
		{
			throw new \ModulIS\Exception\InvalidArgumentException("Invalid type - 'array' expected, '" . get_debug_type($value) . "' given.");
		}

		return $value;
	}


	public static function output($value): array
	{
		$value = \Nette\Utils\Json::decode($value[0], \Nette\Utils\Json::FORCE_ARRAY);

		return $value;
	}
}
