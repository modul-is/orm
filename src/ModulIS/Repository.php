<?php
declare(strict_types=1);

namespace ModulIS;

use ModulIS\Exception\InvalidArgumentException;
use ModulIS\Reflection\EntityType;
use Nette;
use Nette\Database\Context as Context;
use Nette\Database\IRow as NIRow;
use Nette\Database\Table\Selection as NSelection;
use Nette\Utils\Reflection as NReflection;

abstract class Repository
{

	use Nette\SmartObject {
		__call as public netteCall;
	}

	/** @var NdbContext */
	protected $database;

	/** @var string|null */
	protected $table;

	/** @var string|null */
	protected $entity;

	/** @var Transaction */
	private $transaction;


	/** @param  NdbContext $database */
	public function __construct(Context $database)
	{
		$this->database = $database;
		$this->transaction = new Transaction($database->getConnection());
	}


	public function createEntity($row = null): Entity
	{
		$class = $this->getEntityClass();
		return new $class($row);
	}


	public function fetchPairs($key, $value, array $criteria = [], $order = null, $separator = ' ')
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

		$table = $this->getTable()->select($key . ',' . $valueColumn)->where($criteria);

		if($order)
		{
			$table->order($order);
		}

		return $table->fetchPairs($key, $value);
	}


	public function getByID($id): ?Entity
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


	/**
	 * @deprecated Use findBy instead
	 */
	public function findAll(): EntityCollection
	{
		return $this->findBy([]);
	}


	/**
	 * Save single instance from database
	 */
	public function save(Entity $entity)
	{
		return $this->persist($entity);
	}


	/**
	 * Save collection by transaction
	 * @note Array or Arrash hash must have entity inside
	 */
	public function saveCollection($collection)
	{
		if($collection)
		{
			return $this->transaction(function () use($collection)
			{
				foreach($collection as $entity)
				{
					$this->persist($entity);
				}
			});
		}
	}


	protected function createEntityFromSelection(NSelection $selection): ?Entity
	{
		$row = $selection->fetch();
		return $row === null ? null : $this->createEntity($row);
	}


	protected function createCollection($selection, $entity = null, $refTable = null, $refColumn = null): EntityCollection
	{
		return new EntityCollection($selection, $entity === null ? [$this, 'createEntity'] : $entity, $refTable, $refColumn);
	}


	public function persist(Entity $entity): bool
	{
		$this->checkEntity($entity);

		return $this->transaction(function () use($entity)

		{

			$record = $entity->toRecord();
			if($record->hasRow())
			{
				return $record->update();
			}

			$inserted = $this->getTable()
					->insert($record->getModified());

			if(!$inserted instanceof NIRow)
			{
				throw new Exception\InvalidStateException('Insert did not return instance of ' . NIRow::class . '. '
						. 'Does table "' . $this->getTableName() . '" have primary key defined? If so, try cleaning cache.');
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
			return $this->transaction(function () use($record)
			{
				return $record->getRow()->delete() > 0;
			});
		}

		return true;
	}


	protected function getTable($table = null): NSelection
	{
		return $this->database->table($table === null ? $this->getTableName() : $table);
	}


	final protected function getTableName(): string
	{
		if($this->table === null)
		{
			$ref = new \ReflectionClass($this);
			$this->table = EntityType::parseAnnotation($ref, 'table');

			if(!$this->table)
			{
				throw new Exception\InvalidStateException('Table name not set. Use either annotation @table or class member ' . $ref->getName() . '::$table');
			}
		}

		return $this->table;
	}


	final protected function getEntityClass(): string
	{
		if($this->entity === null)
		{
			$ref = new \ReflectionClass($this);
			$annotation = EntityType::parseAnnotation($ref, 'entity');

			if(!$annotation)
			{
				throw new Exception\InvalidStateException('Entity class not set. Use either annotation @entity or class member ' . $ref->getName() . '::$entity');
			}

			$this->entity = NReflection::expandClassName($annotation, $ref);
		}

		return $this->entity;
	}


	final protected function checkEntity(Entity $entity): void
	{
		$class = $this->getEntityClass();

		if(!$entity instanceof $class)
		{
			throw new Exception\InvalidArgumentException("Instance of '$class' expected, '"
				. get_class($entity) . "' given.");
		}
	}


	final protected function transaction(\Closure $callback)
	{
		try
		{
			return $this->transaction->transaction($callback);
		}
		catch(\Exception $e)
		{
			throw $e;
		}
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
	public function removeCollection($collection)
	{
		if($collection && (is_array($collection) || $collection instanceof \Nette\Utils\ArrayHash))
		{
			return $this->transaction(function () use($collection)
			{
				foreach($collection as $entity)
				{
					$this->delete($entity);
				}
			});
		}
		else
		{
			throw new InvalidArgumentException();
		}
	}


	/**
	 * Remove single instance from database by ID              
	 */
	public function removeByID(int $id): bool
	{
		return (bool) $this->getTable()->wherePrimary($id)->delete();
	}
}
