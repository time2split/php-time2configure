<?php
declare(strict_types = 1);
namespace Time2Split\Config\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Config\Configuration;
use Time2Split\Config\Configurations;
use Time2Split\Config\Entries;
use Time2Split\Config\Entry;
use Time2Split\Config\Entry\Map;
use Time2Split\Config\Tests\Help\Producer;
use Time2Split\Config\Tests\Help\Provided;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class MappingTest extends TestCase
{

    private const readTree = [
        'a' => 1,
        'a.a' => 10,
        'a.b' => 11,
        'b' => 2
    ];

    private static function readTreeMapped(Configuration $config, Map $map): array
    {
        $ret = [];

        foreach (self::readTree as $key => $value) {
            $entry = $map->map($config, $key, $value);
            $ret[$entry->key] = $entry->value;
        }
        return $ret;
    }

    private static function mapKey(string $key): string
    {
        return \strtoupper($key);
    }

    private static function mapValue(int $val): int
    {
        return $val + 1;
    }

    private static function configProvider(array $tree): array
    {
        $ret[] = new Provided('tree', [
            new Producer(fn () => Configurations::ofTree($tree))
        ]);
        return $ret;
    }

    private static function getInstructionProvider(bool $read): array
    {
        if (! $read) {
            $mapKey = self::mapKey(...);
            $mapVal = self::mapValue(...);

            $ret[] = new Provided('mapKey', [
                Entries::mapKey(self::mapKey(...))
            ]);
            $ret[] = new Provided('mapEntry', [
                Entries::mapEntry(function ($key, $value) use ($mapKey, $mapVal) {
                    return new Entry($mapKey($key), $mapVal($value));
                })
            ]);
        }
        $ret[] = new Provided('mapValue', [
            Entries::mapValue(self::mapValue(...))
        ]);
        $ret[] = new Provided('mapValueFromKey', [
            Entries::mapValueFromKey(function ($key, $config): int {
                return $config[$key] + 1;
            })
        ]);
        return $ret;
    }

    // ========================================================================
    public static function _testMapOnRead(): iterable
    {
        return Provided::merge(MappingTest::configProvider(self::readTree), MappingTest::getInstructionProvider(true));
    }

    #[DataProvider('_testMapOnRead')]
    public function testMapOnRead(Configuration $config, Map $map): void
    {
        $expect = self::readTreeMapped($config, $map);
        $mapped = Configurations::mapOnRead($config, $map);

        // Read one by one
        foreach ($expect as $k => $v)
            $this->assertSame($v, $mapped[$k], "mapped[$k]==$v");

        $this->assertSame($expect, $mapped->toArray(), 'toArray');

        // Optional
        foreach ($expect as $k => $v) {
            $opt = $mapped->getOptional($k);
            $this->assertTrue($opt->isPresent(), 'isPresent');
            $this->assertSame($v, $opt->get(), "opt->get()==$v");
        }

        // Iterator
        $this->assertSame($expect, \iterator_to_array($mapped), 'iterator');

        // ================================
        // Raw access
        // (Raw value must not be mapped)
        // ================================
        // Iterator
        $this->assertSame(self::readTree, \iterator_to_array($mapped->getRawValueIterator()), 'raw iterator');

        // Optional
        foreach (self::readTree as $k => $v) {
            $opt = $mapped->getOptional($k, false);
            $this->assertTrue($opt->isPresent(), 'isPresent');
            $this->assertSame($v, $opt->get(), "opt->get()==$v");
        }
    }

    // ========================================================================
    public static function _testMapOnSet(): iterable
    {
        return Provided::merge(MappingTest::configProvider([]), MappingTest::getInstructionProvider(false));
    }

    #[DataProvider('_testMapOnSet')]
    public function testMapOnSet(Configuration $config, Map $map): void
    {
        $expect = self::readTreeMapped($config, $map);
        $mapped = Configurations::mapOnSet($config, $map);
        $baseTree = self::readTree;

        foreach ($baseTree as $k => $v) {
            $mapped[$k] = $v;
        }
        $this->assertSame($expect, $mapped->toArray());
    }

    // ========================================================================
    public static function _testMapOnUnset(): iterable
    {
        return Provided::merge(MappingTest::configProvider(self::readTree), MappingTest::getInstructionProvider(false));
    }

    #[DataProvider('_testMapOnUnset')]
    public function testMapOnUnset(Configuration $config, Map $map): void
    {
        $baseTree = $config->toArray();
        $baseKeys = \array_keys($baseTree);

        $unset = [];
        $doConf = Configurations::doOnUnset($config, Entries::consumeEntry(function ($k) use (&$unset) {
            $unset[] = $k;
        }));

        $unset = [];
        foreach ($baseTree as $k => $notUsed)
            unset($doConf[$k]);

        $this->assertEmpty($doConf->toArray());
        $this->assertSame($baseKeys, $unset);

        $unset = [];
        $doConf->merge($baseTree);
        $doConf->clear();
        $this->assertEmpty($doConf->toArray());
        $this->assertSame($baseKeys, $unset);

        unset($notUsed);
        unset($unset);
    }

    // ========================================================================
    public static function _testDoOnRead(): iterable
    {
        return Provided::merge(MappingTest::configProvider(self::readTree));
    }

    #[DataProvider('_testDoOnRead')]
    public function testDoOnRead(Configuration $config): void
    {
        $readed = [];
        $expect = $config->toArray();
        $doOnRead = Configurations::doOnRead($config, Entries::consumeEntry(function ($k, $v) use (&$readed) {
            $readed[$k] = $v;
        }));

        // Read one by one
        $readed = [];
        foreach ($expect as $k => $v)
            $this->assertSame($v, $doOnRead[$k]);

        $this->assertSame($expect, $readed, 'get');

        // Optional
        $readed = [];
        foreach ($expect as $k => $v) {
            $opt = $doOnRead->getOptional($k);
            $this->assertTrue($opt->isPresent(), 'isPresent');
            $this->assertSame($v, $opt->get(), 'opt->get()');
        }
        $this->assertSame($expect, $readed, 'optional');

        // Iterator
        $readed = [];
        \iterator_to_array($doOnRead);
        $this->assertSame($expect, $readed, 'iterator');

        // ================================
        // Raw access
        // (Raw value must not be consumed)
        // ================================

        // Iterator
        $readed = [];
        \iterator_to_array($doOnRead->getRawValueIterator());
        $this->assertSame([], $readed, 'raw iterator');

        // Optional
        $readed = [];
        foreach ($expect as $k => $v) {
            $opt = $doOnRead->getOptional($k, false);
            $this->assertTrue($opt->isPresent(), 'isPresent');
            $this->assertSame($v, $opt->get(), 'opt->get()');
        }
        $this->assertSame([], $readed, 'raw optional');

        // isPresent
        $readed = [];
        foreach ($expect as $k => $v) {
            $this->assertTrue($doOnRead->isPresent($k), 'isPresent');
        }
        $this->assertSame([], $readed, 'isPresent');
    }
}