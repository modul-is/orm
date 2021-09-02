<?php
declare(strict_types=1);

namespace ModulIS\Reflection;

use ModulIS\Entity;

class EntityProperty
{
	private EntityType $reflection;

	private string $name;

	private string $type;

	private bool $nullable;

	private bool $readonly;


	public function __construct(EntityType $reflection, string $name, string $type, bool $nullable, bool $readonly)
	{
		$this->reflection = $reflection;
		$this->name = $name;
		$this->type = $type;
		$this->nullable = $nullable;
		$this->readonly = $readonly;
	}


	public function getValue(Entity $entity): mixed
	{
		$value = $this->setType($entity->toRecord()->{$this->getName()});
		return $value;
	}


	public function setValue(Entity $entity, $value): void
	{
		if($this->isReadonly())
		{
			$ref = $entity::getReflection();
			throw new \ModulIS\Exception\MemberAccessException("Cannot write to a read-only property {$ref->getName()}::\${$this->getName()}.");
		}

		$this->checkType($value);
		$entity->toRecord()->{$this->getName()} = $value;
	}


	public function checkType($value, bool $need = true): bool
	{
		if($value === null)
		{
			if(!$this->nullable)
			{
				$entity = $this->getEntityReflection()->getName();
				throw new \ModulIS\Exception\InvalidArgumentException("Property '{$entity}::\${$this->getName()}' cannot be null.");
			}
		}
		elseif($this->isOfExtraType())
		{
			return true;
		}
		elseif(!$this->isOfNativeType())
		{
			$class = $this->getType();

			if(!($value instanceof $class) && get_parent_class($class) !== 'ModulIS\Datatype\Datatype')
			{
				throw new \ModulIS\Exception\InvalidArgumentException("Instance of '{$class}' expected, '"
					. (($valtype = gettype($value)) === 'object' ? $value::class : $valtype) . "' given.");
			}
		}
		/** @phpstan-ignore-next-line */
		elseif($need && !call_user_func('is_' . $this->getType(), $value) && self::getConvertedType($this->getType()) !== get_debug_type($value))
		{
			throw new \ModulIS\Exception\InvalidArgumentException("Invalid type - '{$this->getType()}' expected, '" . get_debug_type($value) . "' given.");
		}
		else
		{
			return false;
		}

		return true;
	}


	public function getType(): string
	{
		return $this->type;
	}


	public function setType($value): mixed
	{
		/**
		 * Type casting needed
		 */
		if(!$this->checkType($value))
		{
			settype($value, $this->getType());
		}

		return $value;
	}


	public function isOfNativeType(): bool
	{
		return self::isNativeType($this->type);
	}


	public function isOfExtraType(): bool
	{
		return self::isExtraType($this->type);
	}


	private static function getConvertedType(string $type): string
	{
		switch($type)
		{
			case 'array':
				return 'string';
			case 'bool':
				return 'int';
			default:
				return $type;
		}
	}


	private static function isNativeType(string $type): bool
	{
		return in_array($type, ['int', 'float', 'double', 'bool', 'string', 'array'], true);
	}


	private static function isExtraType(string $type): bool
	{
		return $type === \Nette\Utils\DateTime::class;
	}


	public function getEntityReflection(): EntityType
	{
		return $this->reflection;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
	}


	public function isReadonly(): bool
	{
		return $this->readonly;
	}
}
