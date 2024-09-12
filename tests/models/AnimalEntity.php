<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Datatype\DateTimeDatatype;
use ModulIS\Datatype\JsonDatatype;

class AnimalEntity extends \ModulIS\Entity
{
	public int $id;

	public string $name;

	public int $weight;

	#[DateTimeDatatype]
	public \Nette\Utils\DateTime $birth;

	#[JsonDatatype]
	public array $parameters;

	#[DateTimeDatatype]
	public ?string $death;

	public bool $vaccinated;

	public int $height;

	public float $price;
}
