<?php
declare(strict_types=1);

namespace ModulIS\Datatype;

class DateTime extends Datatype
{
	public static function input($value): string
	{
		if($value instanceof \Nette\Utils\DateTime)
		{
			$value = $value->__toString();
		}
		else
		{
			throw new \ModulIS\Exception\InvalidArgumentException("Instance of '\Nette\Utils\Datetime' expected, '" . get_debug_type($value) . "' given.");
		}

		return $value;
	}


	public static function output($value): \Nette\Utils\DateTime
	{
		$value = new \Nette\Utils\DateTime($value);

		return $value;
	}
}