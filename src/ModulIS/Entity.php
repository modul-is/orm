<?php
declare(strict_types=1);

namespace ModulIS;

use ModulIS\Reflection\EntityProperty;

abstract class Entity
{
	protected Record $record;

	private static array $reflections = [];


	public function __construct(Record|\Nette\Database\Table\ActiveRow|null $row = null)
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


	public function &__get(string $name): mixed
	{
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if($prop instanceof EntityProperty)
		{
			$value = $prop->getValue($this);

			if($value !== null)
			{
				if($prop->getType() == 'array')
				{
					$value = \ModulIS\Datatype\Json::output($value);
				}
				elseif($prop->getType() == 'bool')
				{
					$value = \ModulIS\Datatype\Boolean::output($value);
				}
				elseif($prop->getType() == \Nette\Utils\DateTime::class && !$value instanceof \Nette\Utils\DateTime)
				{
					$value = \ModulIS\Datatype\DateTime::output($value);
				}
				elseif(!$prop->isOfNativeType() && !$prop->isOfExtraType() && class_exists($prop->getType()))
				{
					$type = $prop->getType();
					$typeClass = new $type;

					$value = $typeClass::output($value);
				}
			}

			return $value;
		}

		throw new Exception\MemberAccessException("Cannot read an undeclared property {$ref->getName()}::\$$name.");
	}


	public function __set(string $name, $value): void
	{
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if($prop instanceof EntityProperty)
		{
			if($value !== null)
			{
				if($prop->getType() == 'array')
				{
					$value = \ModulIS\Datatype\Json::input($value);
				}
				elseif($prop->getType() == 'bool')
				{
					$value = \ModulIS\Datatype\Boolean::input($value);
				}
				elseif($prop->getType() == \Nette\Utils\DateTime::class)
				{
					$value = \ModulIS\Datatype\DateTime::input($value);
				}
				elseif(!$prop->isOfNativeType() && !$prop->isOfExtraType() && class_exists($prop->getType()))
				{
					$type = $prop->getType();
					$typeClass = new $type;

					$value = $typeClass::input($value->value);
				}
			}

			$prop->setValue($this, $value);

			return;
		}

		throw new Exception\MemberAccessException("Cannot write to an undeclared property {$ref->getName()}::\$$name.");
	}


	public function __isset(string $name): bool
	{
		$prop = static::getReflection()->getEntityProperty($name);

		if($prop instanceof EntityProperty)
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
			if(array_search($name, $excludedProperties, true) !== false || $name === 'modifiedArray')
			{
				continue;
			}

			try
			{
				$values[$name] = $this->$name;
			}
			catch(Exception\MemberAccessException $ex)
			{
				if($property->isNullable())
				{
					$values[$name] = null;
				}
				else
				{
					throw new Exception\MemberAccessException($ex->getMessage());
				}
			}
		}

		return $values;
	}


	/**
	 * Fill entity from array or ArrayHash
	 */
	public function fillFromArray(array|\Nette\Utils\ArrayHash $values): void
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
