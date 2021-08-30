<?php
declare(strict_types=1);

namespace ModulIS\Datatype;

abstract class Datatype
{
	public $value;


	public function __construct
	(
		$value = null
	)
	{
		$this->value = $value;
	}


	abstract public static function input($value);


	abstract public static function output($value);
}