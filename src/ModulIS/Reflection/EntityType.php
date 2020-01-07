<?php
declare(strict_types=1);

namespace ModulIS\Reflection;

use ModulIS;
use Nette\Utils\Reflection as NReflection;
use Nette\Utils\Strings as NStrings;

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
		if(!NReflection::areCommentsAvailable())
		{
			throw new Nette\InvalidStateException('You have to enable phpDoc comments in opcode cache.');
		}
		$re = '#[\s*]@' . preg_quote($name, '#') . '(?=\s|$)(?:[ \t]+([^@\s]\S*))?#';
		if($ref->getDocComment() && $m = NStrings::match(trim($ref->getDocComment(), '/*'), $re))

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

			$matches = NStrings::matchAll(($class::getReflection())->getDocComment(), '/@(\S+) (\S+) ((?:(?!\*\/)\S(?: -> \S)*)*) ?((?:(?!\*\/).)*)/', PREG_SET_ORDER);

			/**
			 * 0 - @property-read int $id desc
			 * 1 - property-read
			 * 2 - int
			 * 3 - $id			 
			 */
			foreach($matches as $match)

			{

				if($match[1] === 'property' || $match[1] === 'property-read')
				{
					if(!(isset($match[3]) && strlen($match[3])) || !(isset($match[2]) && strlen($match[2])))
					{
						throw new ModulIS\Exception\InvalidPropertyDefinitionException('"@property[-read] <type> $<property> [-> <column>][ <description>]" expected, "' . trim($match[0]) . '" given.');
					}

					if(!NStrings::startsWith($match[3], '$'))
					{
						throw new ModulIS\Exception\InvalidPropertyDefinitionException('Missing "$" in property name in "' . trim($match[0]) . '"');
					}

					$nullable = false;
					$type = $match[2];

					$types = explode('|', $type, 2);
					if(count($types) === 2)
					{
						if(strcasecmp($types[0], 'NULL') === 0)
						{
							$nullable = true;
							$type = $types[1];
						}

						if(strcasecmp($types[1], 'NULL') === 0)
						{
							if($nullable)
							{
								throw new ModulIS\Exception\InvalidPropertyDefinitionException('Only one NULL is allowed, "' . $match[2] . '" given.');
							}

							$nullable = true;
							$type = $types[0];
						}

						if(!$nullable)
						{
							throw new ModulIS\Exception\InvalidPropertyDefinitionException('Multiple non-NULL types detected.');
						}
					}

					if($type === 'boolean')
					{
						$type = 'bool';

					}
					elseif($type === 'integer')
					{
						$type = 'int';
					}

					if(!EntityProperty::isNativeType($type))
					{
						$type = NReflection::expandClassName($type, $class::getReflection());
					}

					$readonly = $match[1] === 'property-read';
					$name = trim(substr(NStrings::contains($match[3], '->') ? NStrings::before($match[3], '->') : $match[3], 1));
					$column = trim(substr(NStrings::contains($match[3], '->') ? NStrings::after($match[3], '->') : $match[3], 1));

					self::$annProps[$class][$name] = new AnnotationProperty(
							$class::getReflection(),
							$name,
							$readonly,
							$type,
							$column,
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
