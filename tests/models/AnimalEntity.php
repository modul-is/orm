<?php
declare(strict_types=1);

class AnimalEntity extends \ModulIS\Entity
{
	public int $id;

	public string $name;

	public int $weight;

	public \Nette\Utils\Datetime $birth;

	public array $parameters;

	public \Nette\Utils\Datetime|null $death;

	public int $vaccinated;

	public int $height;
}
