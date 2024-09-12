<?php

declare(strict_types=1);

namespace ModulIS\Datatype;

use Attribute;


#[Attribute]
abstract class Datatype
{
	abstract public static function input(string $name, string $type, $value);


	abstract public static function output(string $type, $value);
}
