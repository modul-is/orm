<?php
declare(strict_types=1);

namespace ModulIS\Reflection;

use ModulIS;
use ModulIS\Exception;
use Nette\Utils\Reflection;
use Nette\Utils\Strings;

class EntityType extends \ReflectionClass
{
	/**
	 * @var EntityProperty[]|null
	 */
	private $properties;


	public function getEntityProperties(): array
	{
		$this->loadEntityProperties();
		return $this->properties;
	}


	public function getEntityProperty($name, $default = null): ?EntityProperty
	{
		return $this->hasEntityProperty($name) ? $this->properties[$name] : $default;
	}


	public function hasEntityProperty($name): bool
	{
		$this->loadEntityProperties();
		return isset($this->properties[$name]);
	}


	private function loadEntityProperties(): void
	{
		if($this->properties === null)
		{
			$this->properties = [];

			foreach($this->getClassTree() as $class)
			{
				foreach($class::getReflection()->getProperties(\ReflectionProperty::IS_PRIVATE) as $key => $property)
				{
					if(!$property->getType())
					{
						throw new Exception\InvalidPropertyDefinitionException('Missing type of property "' . $property->getName() . '"');
					}

					$typeArray = explode('\\', $property->getType()->getName());
					$type = end($typeArray);

					$this->properties[$property->getName()] = new AnnotationProperty(
						$class::getReflection(),
						$property->getName(),
						false,
						$type,
						$property->getType()->allowsNull()
					);
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


	/**
	 * Returns an annotation value.
	 */
	public static function parseAnnotation(\ReflectionClass $ref, string $name): ?string
	{
		if(!Reflection::areCommentsAvailable())
		{
			throw new \ModulIS\Exception\InvalidStateException('You have to enable phpDoc comments in opcode cache.');
		}

		$re = '#[\s*]@' . preg_quote($name, '#') . '(?=\s|$)(?:[ \t]+([^@\s]\S*))?#';

		if($ref->getDocComment() && $m = Strings::match(trim($ref->getDocComment(), '/*'), $re))
		{
			return $m[1] ?? '';
		}

		return null;
	}
}
