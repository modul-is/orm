<?php

declare(strict_types = 1);

namespace ModulIS;

use Countable;
use Iterator;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


/**
 * @template TEntity of Entity
 * @implements Iterator<int, TEntity>
 */
class EntityCollection implements Iterator, Countable
{
	public const ASC = 'ASC';

	public const DESC = 'DESC';

	/** @var list<TEntity>|null */
	protected ?array $data;

	/** @var int<0, max> */
	private int $count;

	private int $position = 0;


	/**
	 * @param Selection<ActiveRow> $selection
	 * @param class-string<TEntity> $entity
	 */
	public function __construct
	(
		protected Selection $selection,
		protected string $entity
	)
	{
	}


	/**
	 * @phpstan-assert !null $this->data
	 */
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


	/**
	 * @return list<TEntity>
	 */
	public function toArray(): array
	{
		$this->loadData();

		return $this->data;
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
	 *
	 * @param string|array<string, string|null> $column
	 * @return $this
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


	/**
	 * @return $this
	 */
	public function limit(?int $limit, ?int $offset = null): self
	{
		$this->selection->limit($limit, $offset);
		$this->invalidate();
		return $this;
	}


	private function invalidate(): void
	{
		$this->data = null;
		$this->position = 0;
	}


	// === \Iterator INTERFACE ======================================

	public function rewind(): void
	{
		$this->loadData();
		$this->position = 0;
	}


	/**
	 * @return TEntity
	 */
	public function current(): Entity
	{
		$this->loadData();

		if(!isset($this->data[$this->position]))
		{
			throw new Exception\InvalidStateException('There is no entity at position ' . $this->position . '.');
		}

		return $this->data[$this->position];
	}


	public function key(): int
	{
		return $this->position;
	}


	public function next(): void
	{
		$this->position++;
	}


	public function valid(): bool
	{
		$this->loadData();

		return isset($this->data[$this->position]);
	}


	// === \Countable INTERFACE ======================================


	public function count(?string $column = null): int
	{
		if($column !== null)
		{
			return $this->countRows($column);
		}

		if(isset($this->data))
		{
			return count($this->data);
		}

		if(!isset($this->count))
		{
			$this->count = $this->countRows('*');
		}

		return $this->count;
	}


	/**
	 * @return int<0, max>
	 */
	private function countRows(string $column): int
	{
		return max(0, $this->selection->count($column));
	}
}
