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
        $builder->from($copy);
        $this->assertTrue(Arrays::contentEquals($flat, $copy->toArray()), "flat equals copy 2");
    }
}