<?php

declare(strict_types=1);

namespace ModulIS\Datatype;

class Enum extends Datatype
{
	public function __construct
	(
		public readonly string $enumClass
	)
	{
	}

	public static function input(string $name, $value): int
	{
		return self::$enumClass::tryFrom($value);
	}


	public static function output($value): bool
	{
		return $value;
	}
}
