<?php

declare(strict_types=1);

namespace ModulIS;

use Nette\Database\Table\Selection;

class EntityCollection implements \Iterator, \Countable
{
	public const ASC = 'ASC';

	public const DESC = 'DESC';

	protected Selection $selection;

	protected string|\Closure $entity;

	protected string|null $refTable;

	protected string|null $refColumn;

	protected ?array $data;

	private int|null $count;

	private array $keys;


	public function __construct(Selection $selection, $entity, $refTable = null, $refColumn = null)
	{
		$this->selection = $selection;
		$this->refTable = $refTable;
		$this->refColumn = $refColumn;

		try
		{
			\Nette\Utils\Callback::check($entity);
			$this->entity = \Closure::fromCallable($entity);

		}
		catch(\Exception $e)
		{
			$this->entity = $entity;
		}
	}


	private function loadData(): void
	{
		if(!isset($this->data))
		{
			if($this->entity instanceof \Closure)
			{
				$factory = $this->entity;

			}
			else
			{
				$class = $this->entity;
				$factory = fn($record) => new $class($record);
			}

			$this->data = [];
			foreach($this->selection as $row)
			{
				$record = $this->refTable === null ? $row : $row->ref($this->refTable, $this->refColumn);
				$this->data[] = $factory($record);
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


	public function limit(?int $limit, int $offset = null): self
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


	public function count(string $column = null): int
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
