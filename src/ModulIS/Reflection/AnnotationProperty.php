<?php
declare(strict_types=1);

namespace ModulIS\Reflection;

use ModulIS\Entity;
use ModulIS\Exception;

class AnnotationProperty extends EntityProperty
{
	/**
	 * @var string
	 */
	private $column;

	/**
	 * @var bool
	 */
	private $nullable;


	public function __construct(EntityType $reflection, string $name, bool $readonly, string $type, bool $nullable)
	{
		parent::__construct($reflection, $name, $readonly, $type);

		$this->column = $name;
		$this->nullable = $nullable;
	}


	/**
	 * @inheritdoc
	 */
	public function getValue(Entity $entity)
	{
		$value = $this->setType($entity->toRecord()->{$this->getColumn()});
		return $value;
	}


	/**
	 * @inheritdoc
	 */
	public function setValue(Entity $entity, $value): void
	{
		if($this->isReadonly())
		{
			$ref = $entity::getReflection();
			throw new Exception\MemberAccessException("Cannot write to a read-only property {$ref->getName()}::\${$this->getName()}.");
		}

		$this->checkType($value);
		$entity->toRecord()->{$this->getColumn()} = $value;
	}


	public function getColumn(): string
	{
		return $this->column;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
	}


	public function checkType($value, bool $need = true): bool
	{
		if($value === null)
		{
			if(!$this->nullable)
			{
				$entity = $this->getEntityReflection()->getName();
				throw new Exception\InvalidArgumentException("Property '{$entity}::\${$this->getName()}' cannot be null.");
			}

		}
		elseif($this->isOfExtraType())
		{
			return true;
		}
		elseif(!$this->isOfNativeType())
		{
			$class = $this->getType();

			if(!($value instanceof $class))
			{
				throw new Exception\InvalidArgumentException("Instance of '{$class}' expected, '"
						. (($valtype = gettype($value)) === 'object' ? get_class($value) : $valtype) . "' given.");
			}

		}
		elseif($need && !call_user_func('is_' . $this->getType(), $value))
		{
			bdump("X");
		{			
			throw new Exception\InvalidArgumentException("Invalid type - '{$this->getType()}' expected, '" . gettype($value) . "' given.");
		}
		else
		{
			return false;
		}

		return true;
	}


	public function setType($value)
	{
		if(!$this->checkType($value, true)){ // type casting needed
			settype($value, $this->getType());
		}

		return $value;
	}

}
