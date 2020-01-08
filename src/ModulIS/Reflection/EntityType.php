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

	/** @var array <class> => AnnotationProperty[] */
	private static $annProps = [];


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
				self::loadAnnotationProperties($class);

				foreach(self::$annProps[$class] as $name => $property)
				{
					if(!isset($this->properties[$name]))
					{
						$this->properties[$name] = $property;
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

		} while($current !== false && $current !== ModulIS\Entity::class);

		return array_reverse($tree);
	}


	/**
	 * Returns an annotation value.
	 */
	public static function parseAnnotation(\Reflector $ref, string $name): ?string
	{
		if(!Reflection::areCommentsAvailable())
		{
			throw new Nette\InvalidStateException('You have to enable phpDoc comments in opcode cache.');
		}
		$re = '#[\s*]@' . preg_quote($name, '#') . '(?=\s|$)(?:[ \t]+([^@\s]\S*))?#';
		if($ref->getDocComment() && $m = Strings::match(trim($ref->getDocComment(), '/*'), $re))

		{

			return $m[1] ?? '';
		}
		return null;
	}


	private static function loadAnnotationProperties(string $class): void
	{
		if(!isset(self::$annProps[$class]))
		{
			self::$annProps[$class] = [];

			$matches = Strings::matchAll(($class::getReflection())->getDocComment(), '/@(\S+) (\S+) (\S+)/', PREG_SET_ORDER);

			/**
			 * 0 - @property-read int $id desc
			 * 1 - property-read
			 * 2 - int|NULL
			 * 3 - $id			 
			 */
			foreach($matches as $match)
			{
				[$result, $property, $type, $name] = $match;

				if($property === 'property' || $property === 'property-read')
				{
					if(!Strings::startsWith($name, '$'))
					{
						throw new Exception\InvalidPropertyDefinitionException('Missing "$" in property name in "' . $name . '" in string "' . $result . '"');
					}

					if(Strings::contains($type, '|'))
					{
						$kind = Strings::before($type, '|');
						$null = Strings::after($type, '|');

						if($kind === 'null')
						{
							throw new Exception\InvalidPropertyDefinitionException('Use null as second parameter like "string|null".');
						}
						else
						{
							$type = $kind;
						}

						if($null === 'null')
						{
							$nullable = true;
						}
						else
						{
							throw new Exception\InvalidPropertyDefinitionException('Use "null" instead of "' . $null . '". Multiple non-null types detected.');
						}
					}
					else
					{
						$nullable = false;
					}

					//if(!EntityProperty::isNativeType($type))
					//{
					//	$type = Reflection::expandClassName($type, $class::getReflection());
					//}

					$column = Strings::after($name, '$');

					self::$annProps[$class][$column] = new AnnotationProperty(
							$class::getReflection(),
							$column,
							$property === 'property-read',
							$type,
							$nullable
					);

				}
			}

		}


	}


	public static function from($class)
	{
		return new static($class);
	}
}
