<?php

declare(strict_types=1);

class AnimalRepository extends \ModulIS\Repository
{
	protected $table = 'animal';

	protected $entity = 'AnimalEntity';


	public function __construct(Nette\Database\Context $context)
	{
		parent::__construct($context);
	}
}
