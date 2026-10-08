<?php

declare(strict_types = 1);

namespace ModulIS\Datatype;


abstract class Datatype
{
	abstract public static function input(string $name, string $type, mixed $value): mixed;


	abstract public static function output(string $type, mixed $value): mixed;


	/**
	 * Value assigned to the property when it has no value yet, NULL when the datatype generates nothing
	 */
	public static function generateDefault(string $type): mixed
	{
		return null;
	}
}
