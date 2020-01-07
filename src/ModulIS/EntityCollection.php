<?php
declare(strict_types=1);

namespace ModulIS;

use Nette\Database\Table\Selection as Selection;
use Nette\Utils\Callback as Callback;


class EntityCollection implements \Iterator, \Countable
{
	public const ASC = false;

	public const DESC = true;

	/**
	 * @var Selection
	 */
	protected $selection;

	/**
	 * @var string|Callback
	 */
	protected $entity;

	/**
	 * @var string|null
	 */
	protected $refTable;

	/**
	 * @var string|null
	 */
	protected $refColumn;

	/**
	 * @var Entity[]|null
	 */
	protected $data;

	/**
	 * @var int|NULL
	 */
	private $count;

	/**
	 * @var array
	 */
	private $keys;


	public function __construct(Selection $selection, $entity, $refTable = null, $refColumn = null)
	{
		$this->selection = $selection;
		$this->refTable = $refTable;
		$this->refColumn = $refColumn;

		try
		{
			Callback::check($entity);
			$this->entity = Callback::closure($entity);

		}
		catch(\Exception $e)
		{
			$this->entity = $entity;
		}
	}


	private function loadData(): void
	{
		if($this->data === null)
		{
			if($this->entity instanceof \Closure)
			{
				$factory = $this->entity;

			}
			else
			{
				$class = $this->entity;
				$factory = function ($record) use($class)
				{
					return new $class($record);
				};
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
	 * $this->orderBy(array(
	 *	'first'  => EntityCollection::ASC,
	 *	'second' => EntityCollection::DESC,
	 * ); // ORDER BY [first], [second] DESC
	 * </code>
	 */
	public function orderBy($column, $dir = null): self
	{
		if(is_array($column))
		{
			foreach($column as $col => $d)
			{
				$this->orderBy($col, $d);
			}
		}
		else
		{
			$this->selection->order($column . ($dir === static::DESC ? ' DESC' : ''));
		}

		$this->invalidate();
		return $this;
	}


	public function limit($limit, $offset = null): self
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


	public function key()
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


	public function count($column = null): int
	{
		if($column !== null)
		{
			return $this->selection->count($column);
		}

		if($this->data !== null)
		{
			return count($this->data);
		}

		if($this->count === null)
		{
			$this->count = $this->selection->count('*');
		}

		return $this->count;
	}

}
