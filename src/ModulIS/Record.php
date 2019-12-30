<?php

namespace ModulIS;

use Nette\Database\Table\ActiveRow as NActiveRow;
use Nette\Database\Table\GroupedSelection as NGroupedSelection;


class Record
{

	/** @var NActiveRow */
	private $row;

	/** @var array */
	private $values = [];

	/** @var array */
	private $modified = [];


	/** @param  NActiveRow $row */
	public function __construct(NActiveRow $row = NULL)
	{
		$this->row = $row;
	}


	/**
	 * @param  NActiveRow|Record $row
	 */
	public static function create($row = NULL): Record
	{
		if ($row === NULL || $row instanceof NActiveRow) {
			return new static($row);

		} elseif ($row instanceof Record) {
			return $row;

		} else {
			throw new Exception\InvalidArgumentException("Instance of 'Nette\Database\Table\ActiveRow' or 'ModulIS\Record' expected, '"
					. (is_object($row) ? get_class($row) : gettype($row))
					. "' given.");
		}
	}


	public function hasRow(): bool
	{
		return $this->row !== NULL;
	}


	public function getRow(): ?NActiveRow
	{
		return $this->row;
	}


	/**
	 * @param  NActiveRow $row
	 */
	public function setRow(NActiveRow $row): Record
	{
		$this->reload($row);
		return $this;
	}


	/**
	 * @param  string $key
	 * @param  string $throughColumn
	 */
	public function ref($key, $throughColumn = NULL): ?Record
	{
		$this->checkRow();
		$native = $this->row->ref($key, $throughColumn);
		return $native instanceof NActiveRow ? new static($native) : NULL;
	}


	/**
	 * @param  string $key
	 * @param  string $throughColumn
	 */
	public function related($key, $throughColumn = NULL): NGroupedSelection
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

		$status = TRUE;
		if (!$this->isPersisted()) {
			$status = $this->row->update($this->modified);
			$this->reload($this->row);
		}

		return $status;
	}


	/**
	 * @param  string $name
	 */
	public function & __get($name)
	{
		if (array_key_exists($name, $this->modified)) {
			return $this->modified[$name];
		}

		if (array_key_exists($name, $this->values)) {
			return $this->values[$name];
		}

		if ($this->row === NULL) {
			throw new Exception\MemberAccessException("The value of column '$name' not set.");
		}

		$native = $this->row->$name;
		$value = $this->values[$name] = $native instanceof NActiveRow ? new static($native) : $native;

		return $value;
	}


	/**
	 * @param  string $name
	 * @param  mixed $value
	 */
	public function __set($name, $value): void
	{
		$this->modified[$name] = $value;
	}


	/**
	 * @param  string $name
	 */
	public function __isset($name): bool
	{
		return isset($this->modified[$name])
			|| isset($this->values[$name])
			|| isset($this->row->$name);
	}


	private function isPersisted(): bool
	{
		return $this->hasRow() && !count($this->modified);
	}


	private function checkRow(): void
	{
		if (!$this->hasRow()) {
			throw new Exception\InvalidStateException('Row not set yet.');
		}
	}


	/**
	 * @param  NActiveRow $row
	 */
	private function reload(NActiveRow $row): void
	{
		$this->row = $row;
		$this->modified = $this->values = [];
	}

}
