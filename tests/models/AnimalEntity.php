<?php

declare(strict_types=1);

namespace ModulIS\Orm;

class AnimalEntity extends \ModulIS\Entity
{
	public int $id;

	public string $name;

	public int $weight;

	#[\ModulIS\Datatype\DateTime]
	public \Nette\Utils\DateTime $birth;

	#[\ModulIS\Datatype\Json]
	public array $parameters;

	#[\ModulIS\Datatype\DateTime]
	public ?string $death;

	public bool $vaccinated;

	public int $height;

	public float $price;
}
