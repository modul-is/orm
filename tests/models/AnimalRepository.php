<?php

declare(strict_types=1);

class AnimalRepository extends \ModulIS\Repository
{
	protected $table = 'animal';

	protected $entity = 'AnimalEntity';


	public function __construct(Nette\Database\Explorer $explorer)
	{
		parent::__construct($explorer);
	}
}
