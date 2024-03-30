<?php

declare(strict_types=1);

namespace Time2Split\Config\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Config\Configurations;
use Time2Split\Help\Arrays;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class TreeConfigTest extends TestCase
{

    public static function builderProvider(): array
    {
        return [
            [
                /* 'content' */
                [
                    'a.a' => 1,
                    'a.b.c' => 10,
                    'a.b' => 'text',
                    'b' => [
                        'c' => null,
                        'd' => 12.3
                    ]
                ],
                /* flat */
                [
                    'a.a' => 1,
                    'a.b' => 'text',
                    'a.b.c' => 10,
                    'b.c' => null,
                    'b.d' => 12.3
                ]
            ]
        ];
    }

    #[DataProvider('builderProvider')]
    public function testBuilder(array $content, array $flat): void
    {
        $builder = Configurations::builder();

        // mergeTree
        $tree = $builder->mergeTree($content);
        $this->assertSame($tree, $builder);
        $this->assertTrue(Arrays::sameEntries($flat, $tree->toArray()), "flat equals tree");

        // build
        $copy = $builder->build();
        $this->assertTrue($copy !== $builder, '$copy !== $builder');
        $this->assertTrue(Arrays::sameEntries($flat, $copy->toArray()), "flat equals copy");

        // from
        $builder->setKeyDelimiter('/');
        $builder->copyOf($copy);
        $this->assertTrue(Arrays::sameEntries($flat, $copy->toArray()), "flat equals copy 2");
    }

    public static function treeProvider(): array
    {
        return [
            [
                fn () => Configurations::builder()
            ],
            [
                fn () => Configurations::builder()->build()
            ],
            [
                fn () => Configurations::emptyChild(Configurations::builder()->build())
            ]
        ];
    }

    #[DataProvider('treeProvider')]
    public function testIsset(\Closure $treeProvider): void
    {
        $tree = $treeProvider();

        $tree['a'] = null;
        $tree['b'] = 0;
        $tree['c.d'] = 0;
        $this->assertSame(3, \count($tree));

        $this->assertFalse(isset($tree['a']), '!isset a');
        $this->assertTrue($tree->isPresent('a'), 'isPresent a');

        $this->assertTrue(isset($tree['b']), 'isset b');
        $this->assertTrue($tree->isPresent('b'), 'isPresent b');

        $this->assertFalse(isset($tree['c']), '!isset c');
        $this->assertFalse($tree->isPresent('c'), '!isPresent c');

        $this->assertFalse(isset($tree['x']), '!isset x');
        $this->assertFalse($tree->isPresent('x'), '!isPresent x');
    }

    #[DataProvider('treeProvider')]
    public function testUnset(\Closure $treeProvider): void
    {
        $tree = $treeProvider();

        $tree['a'] = 0;
        $tree['a.a'] = 1;

        $this->assertTrue(isset($tree['a']), 'isset a');
        $this->assertTrue(isset($tree['a.a']), 'isset a.a');
        $this->assertSame(2, \count($tree)); {
            unset($tree['a']);
            $this->assertSame(1, \count($tree));

            $this->assertFalse(isset($tree['a']), 'unset: !isset a');
            $this->assertFalse($tree->isPresent('a'), 'unset: !isPresent a');
            $this->assertSame(null, $tree['a'], 'unset: null === a');

            $this->assertTrue(isset($tree['a.a']), 'unset: isset a.a 2');
        } {
            $tree->removeNode('a');
            $this->assertSame(0, \count($tree));

            $this->assertFalse(isset($tree['a']), 'unsetNode: !isset a');
            $this->assertFalse($tree->isPresent('a'), 'unsetNode: !isPresent a');
            $this->assertSame(null, $tree['a'], 'unsetNode: null === a');

            $this->assertFalse(isset($tree['a.a']), 'unsetNode: !isset a.a');
            $this->assertFalse($tree->isPresent('a.a'), 'unsetNode: !isPresent a.a');
            $this->assertSame(null, $tree['a.a'], 'unsetNode: null === a.a');
        }

        $tree['a.a'] = 0;
        $this->assertSame(1, \count($tree));
        $tree['a.b'] = 0;
        $this->assertSame(2, \count($tree));
        $tree['b'] = 0;
        $this->assertSame(3, \count($tree));
        $tree->removeNode('a');
        $this->assertSame(1, \count($tree));
    }
}
