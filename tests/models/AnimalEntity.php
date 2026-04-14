<?php

declare(strict_types=1);

namespace ModulIS\Orm;

use ModulIS\Datatype\DateTimeDatatype;
use ModulIS\Datatype\EnumDatatype;
use ModulIS\Datatype\JsonDatatype;
use ModulIS\Entity;
use Nette\Utils\DateTime;


class AnimalEntity extends Entity
{
	public int $id;

	public string $name;

	public int $weight;

	#[DateTimeDatatype]
	public DateTime $birth;

	#[JsonDatatype]
	public array $parameters;

	#[DateTimeDatatype]
	public ?string $death = null;

	public bool $vaccinated;

	public int $height;

	public float $price;

	#[EnumDatatype]
	public ?AnimalEnum $type = null;

	public ?int $zoo_id = null;
}
