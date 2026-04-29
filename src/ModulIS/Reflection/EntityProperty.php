<?php

declare(strict_types = 1);

namespace ModulIS\Reflection;

use ModulIS\Datatype\Datatype;
use ModulIS\Entity;
use ModulIS\Exception\InvalidArgumentException;
use ModulIS\Exception\MemberAccessException;
use Nette\Utils\DateTime;


class EntityProperty
{
	public function __construct
	(
		private EntityType $reflection,
		private string $name,
		private string $type,
		private bool $nullable,
		private bool $readonly,
		private ?Datatype $parser
	)
	{
	}


	public function getValue(Entity $entity): mixed
	{
		$value = $entity->toRecord()->{$this->getName()};

		if($this->parser)
		{
			$value = $this->parser::output($this->getType(), $value);
		}

		$this->checkType($value);

		return $value;
	}


	public function setValue(Entity $entity, $value): void
	{
		if($this->isReadonly())
		{
			$ref = $entity::getReflection();
			throw new MemberAccessException('Cannot write to a read-only property "' . $ref->getName() . '::' . $this->getName() . '"');
		}

		if($this->parser)
		{
			$value = $this->parser::input($this->getName(), $this->getType(), $value);
		}

		$this->checkType($value);

		$entity->toRecord()->{$this->getName()} = $value;
	}


	public function checkType($value): void
	{
		$class = $this->getType();

		if($value === null)
		{
			if(!$this->nullable)
			{
				$entity = $this->getEntityReflection()->getName();
				throw new InvalidArgumentException('Property "' . $entity . '::$' . $this->getName() . '" cannot be null.');
			}
		}
		elseif(!$this->parser instanceof Datatype && !$this->isOfNativeType())
		{
			$valueType = gettype($value);

			if(!$value instanceof $class)
			{
				throw new InvalidArgumentException('Instance of ' . $class . ' expected, ' . ($valueType === 'object' ? $value::class : $valueType) . '" given.');
			}
		}
		elseif($this->isOfNativeType() && !call_user_func('is_' . $this->getType(), $value) && self::getConvertedType($this->getType()) !== get_debug_type($value))
		{
			throw new InvalidArgumentException('Invalid type for column "' . $this->getName() . '" - "' . $this->getType() . '" expected, "' . get_debug_type($value) . '" given.');
		}
	}


	public function getType(): string
	{
		return $this->type;
	}


	public function isOfNativeType(): bool
	{
		return self::isNativeType($this->type);
	}


	public function isOfExtraType(): bool
	{
		return self::isExtraType($this->type);
	}


	public function getParser(): ?Datatype
	{
		return $this->parser;
	}


	private static function getConvertedType(string $type): string
	{
		return match($type)
		{
			'array' => 'string',
			'bool' => 'int',
			default => $type
		};
	}


	private static function isNativeType(string $type): bool
	{
		return in_array($type, ['int', 'float', 'double', 'bool', 'string', 'array'], true);
	}


	private static function isExtraType(string $type): bool
	{
		return $type === DateTime::class || enum_exists($type, false);
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
