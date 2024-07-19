<?php

declare(strict_types=1);

namespace ModulIS\Datatype;

abstract class Datatype
{
	abstract public static function input(string $name, $value);


	abstract public static function output($value);
}
