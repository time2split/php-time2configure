<?php
declare(strict_types = 1);
namespace Time2Split\Config\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Config\Configurations;
use Time2Split\Config\Exception\UnmodifiableException;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class UnmodifiableTest extends TestCase
{

    public static function unmodifiableProvider(): array
    {
        $tree = [
            'a' => 0
        ];
        return [
            [
                fn () => Configurations::unmodifiable(Configurations::ofTree($tree))
            ],
            [
                fn () => Configurations::unmodifiable(Configurations::ofTree($tree))->copy()
            ]
        ];
    }

    #[DataProvider('unmodifiableProvider')]
    public function testSet(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config['a'] = true;
    }

    #[DataProvider('unmodifiableProvider')]
    public function testUnset(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        unset($config['a']);
    }

    #[DataProvider('unmodifiableProvider')]
    public function testRemoveNode(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->removeNode('a');
    }

    #[DataProvider('unmodifiableProvider')]
    public function testMergeTree(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->mergeTree([
            'b' => 1
        ]);
    }

    #[DataProvider('unmodifiableProvider')]
    public function testMerge(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->merge([
            'b' => 1
        ]);
    }

    #[DataProvider('unmodifiableProvider')]
    public function testUnsetFluent(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->unsetFluent('a');
    }

    #[DataProvider('unmodifiableProvider')]
    public function testRemoveNodeFluent(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->removeNodeFluent('a');
    }
}