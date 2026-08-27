<?php

declare(strict_types = 1);

namespace ModulIS;

use ModulIS\Exception\InvalidStateException;
use Nette\Database\Explorer;
use Nette\Database\IRow;
use Nette\Database\ResultSet;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;


/**
 * @template TEntity of Entity
 */
abstract class Repository
{
	protected string $table;

	/** @var class-string<TEntity> */
	protected string $entity;


	public function __construct
	(
		protected Explorer $database
	)
	{
		if(!$this->table)
		{
			$ref = new \ReflectionClass($this);

			throw new InvalidStateException('Table name not set. Use class property ' . $ref->getName() . '::$table');
		}

		if(!$this->entity)
		{
			$ref = new \ReflectionClass($this);

			throw new InvalidStateException('Entity class not set. Use class property ' . $ref->getName() . '::$entity');
		}
	}


	/**
	 * @param string|list<string>|null $value
	 * @param array<int|string, mixed> $criteria
	 * @return array<int|string, mixed>
	 */
	public function fetchPairs(?string $key = null, string|array|null $value = null, array $criteria = [], ?string $order = null, string $separator = ' '): array
	{
		if(is_array($value))
		{
			$valueColumn = 'CONCAT_WS("' . $separator . '", ' . implode(',', $value) . ') AS custom_column';
			$value = 'custom_column';
		}
		else
		{
			$valueColumn = $value;
		}

		$table = $this->getTable()->select($this->table . '.' . $key . ($key && $valueColumn ? ',' : null) . $valueColumn)->where($criteria);

		if($order)
		{
			$table->order($order);
		}

		return $table->fetchPairs($key, $value);
	}


	/**
	 * @return TEntity|null
	 */
	public function getByID(int|string $id)
	{
		$selection = $this->getTable()->wherePrimary($id);
		return $this->createEntityFromSelection($selection);
	}


	/**
	 * @param array<int|string, mixed> $criteria
	 * @return TEntity|null
	 */
	public function getBy(array $criteria)
	{
		$selection = $this->getTable()->where($criteria);
		return $this->createEntityFromSelection($selection);
	}


	/**
	 * @param array<int|string, mixed> $criteria
	 * @return EntityCollection<TEntity>
	 */
	public function findBy(array $criteria): EntityCollection
	{
		$selection = $this->getTable()->where($criteria);
		return $this->createCollection($selection);
	}


	/**
	 * @return EntityCollection<TEntity>
	 */
	public function findAll(): EntityCollection
	{
		return $this->findBy([]);
	}


	/**
	 * Save single instance from database
	 * @param TEntity $entity
	 */
	public function save(Entity $entity): bool
	{
		return $this->persist($entity);
	}


	/**
	 * Save collection by transaction
	 * @note Array or Arrash hash must have entity inside
	 * @param array<TEntity>|EntityCollection<TEntity>|ArrayHash<TEntity> $collection
	 */
	public function saveCollection(array|EntityCollection|ArrayHash $collection): mixed
	{
		if($this->isCollectionEmpty($collection))
		{
			return null;
		}

		$this->transaction(function() use ($collection): void
		{
			foreach($collection as $entity)
			{
				$this->persist($entity);
			}
		});

		return null;
	}


	/**
	 * @param Selection<ActiveRow> $selection
	 * @return TEntity|null
	 */
	protected function createEntityFromSelection(Selection $selection): ?Entity
	{
		$row = $selection->fetch();

		if($row === null)
		{
			return null;
		}

		$class = $this->entity;

		return new $class($row);
	}


	/**
	 * @param Selection<ActiveRow> $selection
	 * @return EntityCollection<TEntity>
	 */
	protected function createCollection(Selection $selection): EntityCollection
	{
		return new EntityCollection($selection, $this->entity);
	}


	/**
	 * @param TEntity $entity
	 */
	public function persist(Entity $entity): bool
	{
		$this->checkEntity($entity);

		return $this->transaction(function() use ($entity)
		{
			$record = $entity->toRecord();
			if($record->hasRow())
			{
				return $record->update();
			}

			$inserted = $this->getTable()
				->insert($record->getModified());

			if(!$inserted instanceof IRow)
			{
				throw new Exception\InvalidStateException('Insert did not return instance of ' . IRow::class . '. '
						. 'Does table "' . $this->table . '" have primary key defined? If so, try cleaning cache.');
			}

			$record->setRow($inserted);
			return true;
		});
	}


	/**
	 * @param TEntity $entity
	 */
	public function delete(Entity $entity): bool
	{
		$this->checkEntity($entity);
		$row = $entity->toRecord()->getRow();

		return $row === null ? true : $row->delete() > 0;
	}


	/**
	 * @return Selection<ActiveRow>
	 */
	public function getTable(?string $table = null): Selection
	{
		return $this->database->table($table ?? $this->table);
	}


	final protected function checkEntity(Entity $entity): void
	{
		$class = $this->entity;

		if(!$entity instanceof $class)
		{
			throw new Exception\InvalidArgumentException('Instance of "' . $class . '" expected, "' . $entity::class . '" given.');
		}
	}


	/**
	 * @template TResult
	 * @param \Closure(): TResult $callback
	 * @return TResult
	 */
	final protected function transaction(\Closure $callback): mixed
	{
		return $this->database->getConnection()->transaction($callback);
	}


	/**
	 * Return ResultSet by custom SQL
	 * @param literal-string $sql
	 */
	public function query(string $sql, mixed ...$params): ResultSet
	{
		return $this->database->query($sql, ...$params);
	}


	/**
	 * @param array<TEntity>|EntityCollection<TEntity>|ArrayHash<TEntity> $collection
	 */
	private function isCollectionEmpty(array|EntityCollection|ArrayHash $collection): bool
	{
		return (!is_array($collection) && $collection->count() === 0) || !$collection;
	}


	/**
	 * Delete collection by transaction
	 * @param array<TEntity>|EntityCollection<TEntity>|ArrayHash<TEntity> $collection
	 */
	public function deleteCollection(array|EntityCollection|ArrayHash $collection): mixed
	{
		if($this->isCollectionEmpty($collection))
		{
			return null;
		}

		$this->transaction(function() use ($collection): void
		{
			foreach($collection as $entity)
			{
				$this->delete($entity);
			}
		});

		return null;
	}


	/**
	 * Delete single instance from database by ID
	 */
	public function deleteByID(int|string $id): bool
	{
		return (bool) $this->getTable()->wherePrimary($id)->delete();
	}


	/**
	 * @deprecated
	 * @param array<TEntity>|EntityCollection<TEntity>|ArrayHash<TEntity> $collection
	 */
	public function removeCollection(array|EntityCollection|ArrayHash $collection): mixed
	{
		return $this->deleteCollection($collection);
	}


	/**
	 * @deprecated
	 */
	public function removeByID(int|string $id): bool
	{
		return $this->deleteByID($id);
	}
}
