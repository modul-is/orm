<?php
declare(strict_types=1);

namespace ModulIS;

use ModulIS\Exception\InvalidArgumentException;
use ModulIS\Reflection\EntityType;
use Nette;
use Nette\Database\Context;
use Nette\Database\IRow;
use Nette\Database\Table\Selection;
use Nette\Utils\Reflection;

abstract class Repository
{
	protected Context $database;

	protected $table;

	protected $entity;

	private Transaction $transaction;


	public function __construct(Context $database)
	{
		$this->database = $database;
		$this->transaction = new Transaction($database->getConnection());

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


	public function getByID(int|string $id): ?Entity
	{
		$selection = $this->getTable()->wherePrimary($id);
		return $this->createEntityFromSelection($selection);
	}


	public function getBy(array $criteria): ?Entity
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
	public function saveCollection(array|EntityCollection|\Nette\Utils\ArrayHash $collection): mixed
	{
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


	/**
	 * @deprecated
	 */
	public function remove(Entity $entity): bool
	{
		return $this->delete($entity);
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
		return $this->transaction->transaction($callback);
	}


	/**
	 * Return ResultSet by custom SQL
	 */
	public function query(string $sql, ...$params): Nette\Database\ResultSet
	{
		return $this->database->query($sql, ...$params);
	}


	/**
	 * Remove collection by transaction
	 */
	public function removeCollection(array|EntityCollection|\Nette\Utils\ArrayHash $collection): mixed
	{
		return $this->transaction(function() use ($collection)
		{
			foreach($collection as $entity)
			{
				$this->delete($entity);
			}
		});
	}


	/**
	 * Remove single instance from database by ID
	 */
	public function removeByID(int|string $id): bool
	{
		return (bool) $this->getTable()->wherePrimary($id)->delete();
	}
}
