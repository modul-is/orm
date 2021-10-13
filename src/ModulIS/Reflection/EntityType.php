<?php
declare(strict_types=1);

namespace ModulIS\Reflection;

use ModulIS;
use ModulIS\Exception;

class EntityType extends \ReflectionClass
{
	private ?array $properties;


	public function getEntityProperties(): array
	{
		$this->loadEntityProperties();
		return $this->properties;
	}


	public function getEntityProperty(string $name): ?EntityProperty
	{
		return $this->hasEntityProperty($name) ? $this->properties[$name] : null;
	}


	public function hasEntityProperty(string $name): bool
	{
		$this->loadEntityProperties();
		return isset($this->properties[$name]);
	}


	private function loadEntityProperties(): void
	{
		if(!isset($this->properties))
		{
			$this->properties = [];

			foreach($this->getClassTree() as $class)
			{
				foreach($class::getReflection()->getProperties() as $property)
				{
					if($property->isPublic())
					{
						if(!$property->getType())
						{
							throw new Exception\InvalidPropertyDefinitionException('Missing type of property "' . $property->getName() . '"');
						}

						$readonly = false;

						foreach($property->getAttributes() as $attribute)
						{
							if($attribute->getName() === 'ModulIS\Readonly')
							{
								$readonly = true;
							}
						}

						$this->properties[$property->getName()] = new EntityProperty(
							$class::getReflection(),
							$property->getName(),
							$property->getType()->getName(),
							$property->getType()->allowsNull(),
							$readonly
						);
					}
				}
			}
		}
	}


	private function getClassTree(): array
	{
		$tree = [];
		$current = $this->getName();

		do
		{
			$tree[] = $current;
			$current = get_parent_class($current);
		}
		while($current !== false && $current !== ModulIS\Entity::class);

		return array_reverse($tree);
	}
}
