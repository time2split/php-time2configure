<?php
declare(strict_types = 1);
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
        $this->assertTrue(Arrays::contentEquals($flat, $tree->toArray()), "flat equals tree");

        // build
        $copy = $builder->build();
        $this->assertTrue($copy !== $builder, '$copy !== $builder');
        $this->assertTrue(Arrays::contentEquals($flat, $copy->toArray()), "flat equals copy");

        // from
        $builder->setKeyDelimiter('/');
        $builder->copyOf($copy);
        $this->assertTrue(Arrays::contentEquals($flat, $copy->toArray()), "flat equals copy 2");
    }

    public static function treeProvider(): array
    {
        return [
            [
                fn () => Configurations::builder()
            ],
            [
                fn () => Configurations::builder()->build()
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
}