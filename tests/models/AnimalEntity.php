<?php

declare(strict_types=1);

class AnimalEntity extends \ModulIS\Entity
{
	public int $id;

	public string $name;

	public int $weight;

	public \Nette\Utils\DateTime $birth;

	public array $parameters;

	public \Nette\Utils\DateTime|null $death;

	public bool $vaccinated;

	public int $height;

	public float $price;
}
