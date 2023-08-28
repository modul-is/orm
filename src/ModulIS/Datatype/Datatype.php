<?php

declare(strict_types=1);

namespace ModulIS\Datatype;

abstract class Datatype
{
	public function __construct
	(
		public string $name,
		public $value = null
	)
	{
	}


	abstract public static function input(string $name, $value);


	abstract public static function output($value);
}
