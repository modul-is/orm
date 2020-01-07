<?php
declare(strict_types=1);

namespace ModulIS\Reflection;

use ModulIS\Entity;

abstract class EntityProperty
{
	/**
	 * @var EntityType
	 */
	private $reflection;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var bool
	 */
	private $readonly;

	/**
	 * @var string
	 */
	private $type;


	public function __construct(EntityType $reflection, string $name, bool $readonly, string $type)
	{
		$this->name = $name;
		$this->type = $type;
		$this->readonly = $readonly;
		$this->reflection = $reflection;
	}


	abstract public function getValue(Entity $entity);


	abstract public function setValue(Entity $entity, $value): void;


	public function getEntityReflection(): EntityType
	{
		return $this->reflection;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function isReadonly(): bool
	{
		return $this->readonly;
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

	public static function isNativeType(string $type): bool
	{
		return $type !== null && in_array($type, ['int', 'float', 'double', 'bool', 'string', 'array'], true);
	}
	
	
	public static function isExtraType(string $type): bool
	{
		return $type !== null && ($type === 'json' || $type == 'date');
	}
	
}
