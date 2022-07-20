# Quickstart

+ [Creating files](#creating-files)
+ [Filling entities](#filling-entities)
+ [Reading entities](#reading-entities)
+ [Saving into database](#saving-into-database)
+ [Reading from database](#reading-from-database)
+ [Deleting database rows](#deleting-database-rows)

Consider the following database:  
![Database schema](https://i.imgur.com/Zba0x7S.png)

### Creating files
We mainly use three types of objects:
1. Entity - carries data in and out of the database
2. Repository - links the entity with its respective table
3. Collection - an iterable object for multiple entities

First we should create entities based on the tables above.

__ZooEntity__
```
namespace Example\Entity;

class ZooEntity extends \ModulIS\Entity
{
	#[\ModulIS\Attribute\ReadonlyProperty]
	public int $id;

	public string $name;
}
```

__AnimalEntity__
```
namespace Example\Entity;

class AnimalEntity extends \ModulIS\Entity
{
	#[\ModulIS\Attribute\ReadonlyProperty]
	public int $id;

	public string $name;

	public int $weight;

	public bool $vaccinated;

	public \Nette\Utils\DateTime $birth;

	public float $price;

	public array|null $parameters;
	
	public int|null $zoo_id
}
```

Then we create a repository for each entity.

__ZooRepository__
```
namespace Example\Repository;

class ZooRepository extends \ModulIS\Repository
{
	protected $table = 'zoo';

	protected $entity = '\Example\Entity\ZooEntity';


	public function getBy(array $criteria): ?\Example\Entity\ZooEntity
	{
		return parent::getBy($criteria);
	}


	public function getByID($id): ?\Example\Entity\ZooEntity
	{
		return parent::getByID($id);
	}
}
```

__AnimalRepository__
```
namespace Example\Repository;

class AnimalRepository extends \ModulIS\Repository
{
	protected $table = 'animal';

	protected $entity = '\Example\Entity\AnimalEntity';


	public function getBy(array $criteria): ?\Example\Entity\AnimalEntity
	{
		return parent::getBy($criteria);
	}


	public function getByID($id): ?\Example\Entity\AnimalEntity
	{
		return parent::getByID($id);
	}
}
```

Repositories have to be registered as services and passed through DI.

__NEON config__
```
services:
	- Example\Repository\ZooRepository
	- Example\Repository\AnimalRepository
```

__Presenter__
```
protected \Example\Repository\ZooRepository $ZooRepository;

protected \Example\Repository\AnimalRepository $AnimalRepository;


public function __construct
(
	\Example\Repository\ZooRepository $ZooRepository,
	\Example\Repository\AnimalRepository $AnimalRepository
)
{
	$this->ZooRepository = $ZooRepository;
	$this->AnimalRepository = $AnimalRepository;
}
```

### Filling entities
To fill an entity with data, simply instantiate it and use it as an object.
```
$animalEntity = new \Example\Entity\AnimalEntity;

$animalEntity->name = 'Kangaroo';
$animalEntity->weight = 15;
$animalEntity->vaccinated = true;
$animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
$animalEntity->price = 199.99
$animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 2];
```

We can also fill it quickly from an array.
```
$array = ['name' => 'My Zoo', 'city' => 'Prague'];

$zooEntity = new \Example\Entity\ZooEntity;

$zooEntity->fillFromArray($array)
```

### Reading entities
We can read the entity as a full object or export the values into an array.
```
bdump($zooEntity);

bdump($animalEntity->toArray());
```

### Saving into database
To save an entity into the database, use the `save()` function of the respective repository.
```
$this->AnimalRepository->save($animalEntity);
```

It is also possible to save multiple entities in an array or even a collection with the `saveCollection()` function.
```
$entityArray = [$zooEntity1, $zooEntity2];

$this->ZooRepository->saveCollection($entityArray);
```

### Reading from database
We have many options for reading from the database, depending on usage.

1. Get an entity - for quick reading/editing
```
$this->ZooRepository->getByID(1);
$this->AnimalRepository->getBy(['name' => 'Kangaroo']);
```
2. Get a collection - to iterate over it and get multiple entities
```
$this->ZooRepository->findBy(['zoo_id' => 1]);
$this->AnimalRepository->findAll();
```
3. Fetch a row - for more complex database operations
```
$this->ZooRepository->getTable()
	->select('id, name, city')
	->where('city', 'Prague')
	->fetch();
```
4. Fetch an array of rows - same usage but for multiple rows
```
$this->AnimalRepository->getTable()
	->select('name, weight, birth, price')
	->where('type >= ?', 10)
	->where('zoo_id IS NOT NULL')
	->order('name DESC')
	->limit(5)
	->fetchAll();
```
5. Fetch pairs - fastest way to get an associative array of two columns
```
$this->ZooRepository->fetchPairs('id', 'name');
```

In some edge cases we have to use `query()` with custom SQL. Then we can use `fetch()`/`fetchAll()`/`fetchPairs()` on the result.
```
$this->ExamRepository->query('
	SELECT zoo.id, COUNT(animal.id) AS count
	FROM animal
	LEFT JOIN zoo ON zoo.id = animal.zoo_id
	WHERE zoo.city != ?
	GROUP BY zoo.id
	', 'Prague')->fetchAll();
```

### Deleting database rows
To delete a single row, use `delete()` with an entity.
```
$this->AnimalRepository->delete($animalEntity);
```

To delete multiple rows, use `removeCollection()` with an array or a collection.
```
$entityArray = [$zooEntity1, $zooEntity2];

$this->ZooRepository->removeCollection($entityArray);
```

Alternatively we can delete a row only using its ID.
```
$this->ZooRepository->removeByID(1);
```