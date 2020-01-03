<?php 

require_once __DIR__ . '/../boostrap.php';

use Tester\Assert;

/**
 * @testCase
 */
class EntityCaseTest extends \Tester\TestCase
{
    protected $cache;

    public function setUp()
    {
        $path = join(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'temp']);

        $this->cache = new Nette\Caching\Storages\FileStorage($path);
        $this->cache->clean([\Nette\Caching\Cache::ALL]);
    }

    public function testInt()
    {
        $a = 1 + 2;
        
        Assert::same(3, $a);
    }
}

$testCase = new EntityCaseTest();
$testCase->run();