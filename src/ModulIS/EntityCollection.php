<?php

declare(strict_types=1);

namespace ModulIS;

use Nette\Database\Table\Selection;

class EntityCollection implements \Iterator, \Countable
{
	public const ASC = 'ASC';

	public const DESC = 'DESC';

	protected ?array $data;

	private ?int $count;

	private array $keys;


	public function __construct
	(
		protected Selection $selection,
		protected string $entity
	)
	{
	}


	private function loadData(): void
	{
		if(!isset($this->data))
		{
			$class = $this->entity;

			$this->data = [];

			foreach($this->selection as $row)
			{
				$this->data[] = new $class($row);
			}
		}
	}


	public function toArray(): array
	{
		return iterator_to_array($this);
	}


	/**
	 * API:
	 *
	 * <code>
	 * $this->orderBy('column', EntityCollection::DESC); // ORDER BY [column] DESC
	 * // or
	 * $this->orderBy('column DESC'); // ORDER BY [column] DESC
	 * // or
	 * $this->orderBy([
	 *	'first'  => EntityCollection::ASC,
	 *	'second' => EntityCollection::DESC,
	 * ]; // ORDER BY [first], [second] DESC
	 * </code>
	 */
	public function orderBy(string|array $column, ?string $order = null): self
	{
		if(is_array($column))
		{
			foreach($column as $col => $ord)
			{
				$this->orderBy($col, $ord);
			}
		}
		else
		{
			$this->selection->order($column . ($order ? ' ' . $order : ''));
		}

		$this->invalidate();
		return $this;
	}


	public function limit(?int $limit, ?int $offset = null): self
	{
		$this->selection->limit($limit, $offset);
		$this->invalidate();
		return $this;
	}


	private function invalidate(): void
	{
		$this->data = null;
	}


	// === \Iterator INTERFACE ======================================

	public function rewind(): void
	{
		$this->loadData();
		$this->keys = array_keys($this->data);
		reset($this->keys);
	}


	public function current(): Entity
	{
		$key = current($this->keys);
		return $key === false ? false : $this->data[$key];
	}


	public function key(): mixed
	{
		return current($this->keys);
	}


	public function next(): void
	{
		next($this->keys);
	}


	public function valid(): bool
	{
		return current($this->keys) !== false;
	}


	// === \Countable INTERFACE ======================================


	public function count(?string $column = null): int
	{
		if($column !== null)
		{
			return $this->selection->count($column);
		}

		if(isset($this->data))
		{
			return count($this->data);
		}

		if(!isset($this->count))
		{
			$this->count = $this->selection->count('*');
		}

		return $this->count;
	}
}
