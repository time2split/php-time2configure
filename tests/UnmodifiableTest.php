<?php

declare(strict_types=1);

namespace Time2Split\Config\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
                fn() => Configurations::unmodifiable(Configurations::ofTree($tree))
            ],
            [
                fn() => Configurations::unmodifiable(Configurations::ofTree($tree))->copy()
            ],
            [
                fn() => clone Configurations::unmodifiable(Configurations::ofTree($tree))
            ]
        ];
    }

    #[Test]
    #[DataProvider('unmodifiableProvider')]
    public function set(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config['a'] = true;
        unset($config);
    }

    #[Test]
    #[DataProvider('unmodifiableProvider')]
    public function unset(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        unset($config['a']);
    }

    #[Test]
    #[DataProvider('unmodifiableProvider')]
    public function removeNode(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->offsetUnsetNode('a');
    }

    #[Test]
    #[DataProvider('unmodifiableProvider')]
    public function mergeTree(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->mergeTree([
            'b' => 1
        ]);
    }

    #[Test]
    #[DataProvider('unmodifiableProvider')]
    public function merge(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->merge([
            'b' => 1
        ]);
    }

    #[Test]
    #[DataProvider('unmodifiableProvider')]
    public function unsetMore(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->unsetMore('a');
    }

    #[Test]
    #[DataProvider('unmodifiableProvider')]
    public function unsetNode(\Closure $provider): void
    {
        $config = $provider();
        $this->expectException(UnmodifiableException::class);
        $config->unsetNode('a');
    }
}
