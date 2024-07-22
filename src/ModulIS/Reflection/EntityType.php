<?php

declare(strict_types=1);

namespace ModulIS\Reflection;

use ModulIS;
use ModulIS\Exception;

class EntityType extends \ReflectionClass
{
	private array $properties = [];


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
		if($this->properties !== [])
		{
			return;
		}

		/**
		 * Entity might extend another one - collect all properties
		 */
		foreach($this->getClassTree() as $class)
		{
			foreach($class::getReflection()->getProperties() as $property)
			{
				if(!$property->isPublic())
				{
					continue;
				}

				$propertyType = $property->getType();

				if(!$propertyType)
				{
					throw new Exception\InvalidPropertyDefinitionException('Missing type of property "' . $property->getName() . '"');
				}

				$readonly = false;
				$parser = null;

				/**
				 * Basic parser
				 */
				if($propertyType == 'bool')
				{
					$parser = \ModulIS\Datatype\Boolean::class;
				}

				foreach($property->getAttributes() as $attribute)
				{
					$attributeName = $attribute->getName();

					if(in_array($attributeName, [\ModulIS\Attribute\ReadonlyProperty::class, \ModulIS\Attribute\VirtualProperty::class], true))
					{
						$readonly = true;
					}

					if(is_subclass_of($attributeName, \ModulIS\Datatype\Datatype::class, true))
					{
						$parser = new $attributeName;
					}
				}

				$this->properties[$property->getName()] = new EntityProperty(
					$class::getReflection(),
					$property->getName(),
					$propertyType->getName(),
					$propertyType->allowsNull(),
					$readonly,
					$parser
				);
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
