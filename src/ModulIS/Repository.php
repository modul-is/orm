<?php

declare(strict_types=1);

namespace ModulIS;

use Nette;
use Nette\Database\Explorer;
use Nette\Database\IRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;

abstract class Repository
{
	protected string $table;

	protected string $entity;


	public function __construct(protected Explorer $database)
	{
		$ref = new \ReflectionClass($this);

		if(!$this->table)
		{
			throw new Exception\InvalidStateException('Table name not set. Use class property ' . $ref->getName() . '::$table');
		}

		if(!$this->entity)
		{
			throw new Exception\InvalidStateException('Entity class not set. Use class property ' . $ref->getName() . '::$entity');
		}
	}


	public function createEntity(?\Nette\Database\Table\ActiveRow $row = null): Entity
	{
		$class = $this->entity;
		return new $class($row);
	}


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


	public function getByID(int|string $id)
	{
		$selection = $this->getTable()->wherePrimary($id);
		return $this->createEntityFromSelection($selection);
	}


	public function getBy(array $criteria)
	{
		$selection = $this->getTable()->where($criteria);
		return $this->createEntityFromSelection($selection);
	}


	public function findBy(array $criteria): EntityCollection
	{
		$selection = $this->getTable()->where($criteria);
		return $this->createCollection($selection);
	}


	public function findAll(): EntityCollection
	{
		return $this->findBy([]);
	}


	/**
	 * Save single instance from database
	 */
	public function save(Entity $entity): bool
	{
		return $this->persist($entity);
	}


	/**
	 * Save collection by transaction
	 * @note Array or Arrash hash must have entity inside
	 */
	public function saveCollection(array|EntityCollection|ArrayHash $collection): mixed
	{
		if($this->isCollectionEmpty($collection))
		{
			return null;
		}

		return $this->transaction(function() use ($collection)
		{
			foreach($collection as $entity)
			{
				$this->persist($entity);
			}
		});
	}


	protected function createEntityFromSelection(Selection $selection): ?Entity
	{
		$row = $selection->fetch();
		return $row === null ? null : $this->createEntity($row);
	}


	protected function createCollection(Selection $selection, $entity = null, $refTable = null, $refColumn = null): EntityCollection
	{
		return new EntityCollection($selection, $entity ?? [$this, 'createEntity'], $refTable, $refColumn);
	}


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


	public function delete(Entity $entity): bool
	{
		$this->checkEntity($entity);
		$record = $entity->toRecord();

		if($record->hasRow())
		{
			return $this->transaction(fn() => $record->getRow()->delete() > 0);
		}

		return true;
	}


	public function getTable(?string $table = null): Selection
	{
		return $this->database->table($table ?? $this->table);
	}


	final protected function checkEntity(Entity $entity): void
	{
		$class = $this->entity;

		if(!$entity instanceof $class)
		{
			throw new Exception\InvalidArgumentException("Instance of '$class' expected, '"
				. $entity::class . "' given.");
		}
	}


	final protected function transaction(\Closure $callback): mixed
	{
		return $this->database->getConnection()->transaction($callback);
	}


	/**
	 * Return ResultSet by custom SQL
	 */
	public function query(string $sql, ...$params): Nette\Database\ResultSet
	{
		return $this->database->query($sql, ...$params);
	}


	private function isCollectionEmpty(array|EntityCollection|ArrayHash $collection): bool
	{
		return (!is_array($collection) && $collection->count() === 0) || !$collection;
	}


	/**
	 * Delete collection by transaction
	 */
	public function deleteCollection(array|EntityCollection|ArrayHash $collection): mixed
	{
		if($this->isCollectionEmpty($collection))
		{
			return null;
		}

		return $this->transaction(function() use ($collection)
		{
			foreach($collection as $entity)
			{
				$this->delete($entity);
			}
		});
	}


	/**
	 * Delete single instance from database by ID
	 */
	public function deleteByID(int|string $id): bool
	{
		return (bool) $this->getTable()->wherePrimary($id)->delete();
	}
}
