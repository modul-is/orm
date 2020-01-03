<?php
declare(strict_types=1);

namespace ModulIS\Reflection;

use ModulIS\Entity;


class MethodProperty extends EntityProperty
{

	/** @inheritdoc */
	public function getValue(Entity $entity)
	{
		return $entity->{'get' . ucfirst($this->getName())}();
	}


	/** @inheritdoc */
	public function setValue(Entity $entity, $value): void
	{
		$entity->{'set' . ucfirst($this->getName())}($value);
	}

}
