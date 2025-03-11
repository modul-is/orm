<?php

declare(strict_types=1);

namespace ModulIS;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;

class Record
{
	private array $values = [];

	private array $modified = [];


	final public function __construct
	(
		private ?ActiveRow $row = null
	)
	{
	}


	public static function create(ActiveRow|self|null $row = null): self
	{
		if($row === null || $row instanceof ActiveRow)
		{
			return new static($row);
		}
		else
		{
			return $row;
		}
	}


	public function hasRow(): bool
	{
		return $this->row !== null;
	}


	public function getRow(): ?ActiveRow
	{
		return $this->row;
	}


	public function setRow(ActiveRow $row): self
	{
		$this->reload($row);
		return $this;
	}


	public function ref(string $key, ?string $throughColumn = null): ?self
	{
		$this->checkRow();
		$native = $this->row->ref($key, $throughColumn);
		return $native instanceof ActiveRow ? new static($native) : null;
	}


	public function related(string $key, ?string $throughColumn = null): GroupedSelection
	{
		$this->checkRow();
		return $this->row->related($key, $throughColumn);
	}


	public function getModified(): array
	{
		return $this->modified;
	}


	public function update(): bool
	{
		$this->checkRow();

		$status = true;
		if(!$this->isPersisted())
		{
			$status = $this->row->update($this->modified);
			$this->reload($this->row);
		}

		return $status;
	}


	public function &__get(string $name): mixed
	{
		if(array_key_exists($name, $this->modified))
		{
			return $this->modified[$name];
		}

		if(array_key_exists($name, $this->values))
		{
			return $this->values[$name];
		}

		if($this->row === null)
		{
			throw new Exception\MemberAccessException("The value of column '$name' not set.");
		}

		$native = $this->row->$name;
		$value = $this->values[$name] = $native instanceof ActiveRow ? new static($native) : $native;

		return $value;
	}


	public function __set(string $name, $value): void
	{
		$this->modified[$name] = $value;
	}


	public function __isset(string $name): bool
	{
		return isset($this->modified[$name]) || isset($this->values[$name]) || isset($this->row->$name);
	}


	private function isPersisted(): bool
	{
		return $this->hasRow() && !count($this->modified);
	}


	private function checkRow(): void
	{
		if(!$this->hasRow())
		{
			throw new Exception\InvalidStateException('Row not set yet.');
		}
	}


	private function reload(ActiveRow $row): void
	{
		$this->row = $row;
		$this->modified = $this->values = [];
	}
}
