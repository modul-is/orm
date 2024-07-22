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

		if(!$prop instanceof EntityProperty)
		{
			throw new Exception\MemberAccessException("Cannot read an undeclared property {$ref->getName()}::\$$name.");
		}

		$value = $prop->getValue($this);

		return $value;
	}


	public function __set(string $name, $value): void
	{
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if(!$prop instanceof EntityProperty)
		{
			throw new Exception\MemberAccessException("Cannot write to an undeclared property {$ref->getName()}::\$$name.");
		}

		$prop->setValue($this, $value);
	}


	public function __isset(string $name): bool
	{
		$prop = static::getReflection()->getEntityProperty($name);

		try
		{
			if($prop instanceof EntityProperty)
			{
				return $prop->getValue($this) !== null;
			}
		}
		catch(\ModulIS\Exception\MemberAccessException $e)
		{
			return false;
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
			if($property->isReadonly())
			{
				continue;
			}

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
