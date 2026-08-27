<?php

declare(strict_types = 1);

namespace ModulIS\Reflection;

use ModulIS\Attribute\ReadonlyProperty;
use ModulIS\Attribute\VirtualProperty;
use ModulIS\Datatype\BooleanDatatype;
use ModulIS\Datatype\Datatype;
use ModulIS\Entity;
use ModulIS\Exception\InvalidPropertyDefinitionException;
use ModulIS\Exception\MissingAttributeException;


/**
 * @extends \ReflectionClass<Entity>
 */
class EntityType extends \ReflectionClass
{
	/** @var array<string, EntityProperty> */
	private array $properties = [];


	/**
	 * @return array<string, EntityProperty>
	 */
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
					throw new InvalidPropertyDefinitionException('Missing type of property "' . $property->getName() . '"');
				}

				if(!$propertyType instanceof \ReflectionNamedType)
				{
					throw new InvalidPropertyDefinitionException('Union and intersection types are not supported - property "' . $property->getName() . '"');
				}

				$propertyTypeClean = str_replace(['?', '|', 'null'], '', (string) $propertyType);

				if(!in_array($propertyTypeClean, ['int', 'string', 'bool', 'float'], true) && !$property->getAttributes())
				{
					throw new MissingAttributeException('Property "' . $property->getName() . '" of type "' . $propertyType . '" cannot be used without a datatype attribute');
				}

				$readonly = false;
				$parser = null;

				/**
				 * Basic parser
				 */
				if($propertyType == 'bool')
				{
					$parser = new BooleanDatatype;
				}

				foreach($property->getAttributes() as $attribute)
				{
					$attributeName = $attribute->getName();

					if(in_array($attributeName, [ReadonlyProperty::class, VirtualProperty::class], true))
					{
						$readonly = true;
					}

					if(is_subclass_of($attributeName, Datatype::class))
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


	/**
	 * @return list<class-string<Entity>>
	 */
	private function getClassTree(): array
	{
		$tree = [];
		$current = $this->getName();

		while($current !== null)
		{
			$tree[] = $current;
			$parent = get_parent_class($current);
			$current = $parent !== false && is_subclass_of($parent, Entity::class) ? $parent : null;
		}

		return array_reverse($tree);
	}
}
