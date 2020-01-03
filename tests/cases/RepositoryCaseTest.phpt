<?php 

require_once __DIR__ . '/../boostrap.php';

use Tester\Assert;

/**
 * @testCase
 */
class RepositoryCaseTest extends \Tester\TestCase
{
    protected $cache;
    
    public function setUp()
    {
        $this->cache = new Nette\Caching\Storages\FileStorage(__DIR__ . '/../../temp');
        $this->cache->clean([\Nette\Caching\Cache::ALL]);
    }

    public function testInt()
    {
        $a = 8 - 4;

        Assert::same(4, $a);
        Assert::notSame(12, $a * $a);
    }

    public function testNull()
    {
        $a = null;

        Assert::null($a);
    }
}

$testCase = new RepositoryCaseTest();
$testCase->run();