<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;

/**
 * @testCase
 */
class EntityCaseTest extends \Tester\TestCase
{
    protected $Service;

    public function setUp()
    {
        $this->Service = new Service;
        $this->Service->cache->clean([\Nette\Caching\Cache::ALL]);
    }

    /**
     * If possible set NULL thruw new reflection?
     */
    public function testEntitySetNull()
    {
        $zooEntity = new ZooEntity;
        $zooEntity->name = 'Zoo Pilsen';
        $zooEntity->motto = null;

        Assert::same(null, $zooEntity->motto);
    }
	
	/**
     * Entity to Array
     */
    public function testEntityToArray()
    {
        $animalEntity = new AnimalEntity;
        $animalEntity->name = 'Kangaroo';
        $animalEntity->weight = 15;
        $animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
        $animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];
        $animalEntity->death = null;
        $animalEntity->vaccinated = true;
        $animalEntity->height = 50;
		$animalEntity->price = 999.90;

        $array = $animalEntity->toArray(['id']);

        Assert::true(is_array($array));
    }
	
	    /**
     * Entity filled from Array
     */
    public function testEntityFromArray()
    {
        $array = [
            'name' => 'Kangaroo',
            'weight' => 15,
            'birth' => new Nette\Utils\DateTime,
            'parameters' => [
                'color' => 'brown',
                'ears' => 2,
                'eyes' => 1
            ],
            'death' => null,
            'height' => '50',
            'vaccinated' => true
        ];

        $kangarooEntity = new AnimalEntity;
        $kangarooEntity->fillFromArray($array);

        Assert::same(15, $kangarooEntity->weight);

        /**
         * TEST: bool to int conversion
         */
        Assert::same(true, $kangarooEntity->vaccinated);

        /**
         * TEST: string to int conversion
         */
        Assert::same(50, $kangarooEntity->height);

        /**
         * TEST: Filling null values from array
         */
        Assert::same(null, $kangarooEntity->death);
    }
	
	/**
     * Entity save to database
     */
    public function testEntitySaveToDatabase()
    {
        $animalEntity = new AnimalEntity;
        $animalEntity->name = 'Kangaroo';
        $animalEntity->weight = 15;
        $animalEntity->birth = new \Nette\Utils\DateTime('2015-01-01 12:00:00');
        $animalEntity->parameters = ['color' => 'brown', 'ears' => 2, 'eyes' => 1];
        $animalEntity->death = null;
        $animalEntity->vaccinated = true;
        $animalEntity->height = 50;

        $repository = new AnimalRepository($this->Service->database);
        $repository->save($animalEntity);

        /* @var $loadedEntity AnimalEntity */
        $loadedEntity = $repository->getBy(['name' => 'Kangaroo']);

        /**
         * TEST: save entity to database
         */
        Assert::true($loadedEntity instanceof \ModulIS\Entity);

        /**
         * TEST: save & load it back like array via JSON
         */
        Assert::same(['color' => 'brown', 'ears' => 2, 'eyes' => 1], $loadedEntity->parameters);

        /**
         * TEST: save & load \Nette\Utils\DateTime
         */
        Assert::true($loadedEntity->birth instanceof \Nette\Utils\DateTime);

        /**
         * TEST: check right type of date
         */
        Assert::same($loadedEntity->birth->format('Y'), '2015');
        Assert::same($loadedEntity->birth->format('m-d'), '01-01');
        Assert::same($loadedEntity->birth->format('H:i:s'), '12:00:00');
    }
}

$testCase = new EntityCaseTest;
$testCase->run();