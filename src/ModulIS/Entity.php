<?php
declare(strict_types=1);

namespace ModulIS;

use ModulIS\Reflection\AnnotationProperty;
use Nette\Utils\Json;

abstract class Entity
{
	protected Record $record;

	/**
	 * @var array
	 */
	private static $reflections = [];


	public function __construct($row = null)
	{
		$this->record = Record::create($row);
		$ref = static::getReflection();

		foreach($ref->getEntityProperties() as $key => $property)
		{
			unset($this->$key);
		}
	}


	final public function toRecord(): Record
	{
		return $this->record;
	}


	public function __call($name, $args): void
	{
		// events support
		$ref = static::getReflection();

		if(preg_match('#^on[A-Z]#', $name) && $ref->hasProperty($name))
		{
			$prop = $ref->getProperty($name);
			if($prop->isPublic() && !$prop->isStatic() && (is_array($this->$name) || $this->$name instanceof \Traversable))
			{
				foreach($this->$name as $cb)
				{
					$cb($args);
				}

				return;
			}
		}

		$class = static::class;
		throw new Exception\MemberAccessException("Call to undefined method $class::$name().");
	}


	public function &__get($name)
	{
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if($prop instanceof AnnotationProperty)
		{
			$value = $prop->getValue($this);

			if($prop->getType() == 'array')
			{
				if($value !== null)
				{
					$value = \ModulIS\Datatype\Json::output($value);
				}
			}
			elseif($prop->getType() == 'bool')
			{
				if($value !== null)
				{
					$value = \ModulIS\Datatype\Boolean::output($value);
				}
			}
			elseif($prop->getType() == 'Nette\Utils\DateTime')
			{
				if($value !== null)
				{
					$value = \ModulIS\Datatype\DateTime::output($value);
				}
			}
			elseif(!$prop->isOfNativeType() && !$prop->isOfExtraType() && class_exists($prop->getType()))
			{
				$type = $prop->getType();

				$typeClass = new $type();

				$value = $typeClass::output($value);
			}

			return $value;
		}

		throw new Exception\MemberAccessException("Cannot read an undeclared property {$ref->getName()}::\$$name.");
	}


	public function __set($name, $value): void
	{
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if($prop instanceof AnnotationProperty)
		{
			if($prop->getType() == 'array')
			{
				$value = \ModulIS\Datatype\Json::input($value);
			}
			elseif($prop->getType() == 'bool')
			{
				$value = \ModulIS\Datatype\Boolean::input($value);
			}
			elseif($prop->getType() == 'Nette\Utils\DateTime')
			{
				$value = \ModulIS\Datatype\DateTime::input($value);
			}
			elseif(!$prop->isOfNativeType() && !$prop->isOfExtraType() && class_exists($prop->getType()))
			{
				$value = $value::input($value->value);
			}

			$prop->setValue($this, $value);

			return;
		}

		throw new Exception\MemberAccessException("Cannot write to an undeclared property {$ref->getName()}::\$$name.");
	}


	public function __isset($name): bool
	{
		$prop = static::getReflection()->getEntityProperty($name);

		if($prop instanceof AnnotationProperty)
		{
			return $prop->getValue($this) !== null;
		}

		return false;
	}


	public static function getReflection(): Reflection\EntityType
	{
		$class = static::class;
		if(!isset(self::$reflections[$class]))
		{
			self::$reflections[$class] = new Reflection\EntityType($class);
		}

		return self::$reflections[$class];
	}


	public function getModifiedArray(): array
	{
		return $this->record->getModified();
	}


	public function toArray(array $excludedProperties = []): array
	{
		$ref = static::getReflection();
		$values = [];

		foreach($ref->getEntityProperties() as $name => $property)
		{
			if(array_search($name, $excludedProperties, true) === false && $name != 'modifiedArray')
			{
				$values[$name] = (!empty($this->$name) || $this->$name === 0) ? $this->$name : null;
			}
		}

		return $values;
	}


	/**
	 * Fill entity from array or ArrayHash
	 */
	public function fillFromArray($values): void
	{
		$ref = static::getReflection();

		foreach($ref->getEntityProperties() as $name => $property)
		{
			if(!$property->isReadonly())
			{
				/**
				 * Set NULL for nullable properties without value
				 * Skip if property not set and is not nullable
				 */
				if(!isset($values[$name]) && $property->isNullable() && empty($values[$name]))
				{
					$values[$name] = null;
				}
				elseif(!isset($values[$name]) && !$property->isNullable())
				{
					continue;
				}

				/**
				 * Convert strings to int
				 */
				if($property->getType() == 'int' && !empty($values[$name]))
				{
					$this->$name = intval($values[$name]);
				}
				else
				{
					$this->$name = $values[$name];
				}
			}
		}
	}
}
