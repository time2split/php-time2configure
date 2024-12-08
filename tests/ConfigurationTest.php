<?php

declare(strict_types=1);

namespace Time2Split\Config\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Time2Split\Config\Configuration;
use Time2Split\Config\Configurations;
use Time2Split\Config\Exception\UnmodifiableException;
use Time2Split\Help\Iterables;
use Time2Split\Help\Tests\DataProvider\Producer;
use Time2Split\Help\Tests\DataProvider\Provided;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class ConfigurationTest extends TestCase
{

    public static function getConfigProvidersLabeled(array ...$configs): array
    {
        $mconfigs = \array_merge(...$configs);
        return [
            'simple' => new Provided('simple', [new Producer(fn() => Configurations::ofTree($mconfigs))]),
            'builder' => new Provided('builder', [new Producer(fn() => Configurations::builder()->mergeTree($mconfigs))]),
            'childs' => new Provided('childs', [
                new Producer(function () use ($configs) {
                    $configs = \array_reverse($configs);

                    $config = Configurations::ofTree(\array_pop($configs));

                    while (!empty($configs)) {
                        $config = Configurations::emptyChild($config);
                        $config->merge(Configurations::ofTree(\array_pop($configs)));
                    }
                    return $config;
                })
            ]),
            'hierarchy' => new Provided('hierarchy', [
                new Producer(fn() => Configurations::hierarchy(...\array_map(Configurations::ofTree(...), $configs)))
            ])
        ];
    }

    public static function getConfigProviders(array ...$configs): array
    {
        return \array_values(self::getConfigProvidersLabeled(...$configs));
    }

    public static function configProvider(): iterable
    {
        $ret = new \AppendIterator();

        // ====================================================================
        $initial = [
            'a' => 0,
            'a.a' => 1,
            'b' => [
                'a' => 10,
                'b' => [
                    'a' => 100,
                    'b' => 200
                ]
            ]
        ];
        $flat = [
            'a' => 0,
            'a.a' => 1,
            'b.a' => 10,
            'b.b.a' => 100,
            'b.b.b' => 200
        ];
        $absent = [
            'a.b',
            'b',
            'b.b',
            'b.b.c',
            'x'
        ];

        $merger = Provided::merge(
            self::getConfigProviders($initial),
            [new Provided('1', [$flat, $absent])]
        );
        $ret->append($merger);

        // ====================================================================
        $over = [
            'A' => 'vA',
            'A.A' => 'VA.A',
            'A.B' => 'vA.B',
            'b' => 'vb',
            'a' => [
                'b' => 2
            ]
        ];
        $overFlat = [
            'A' => 'vA',
            'A.A' => 'VA.A',
            'A.B' => 'vA.B',
            'b' => 'vb',
            'a' => 0,
            'a.a' => 1,
            'a.b' => 2,
            'b.a' => 10,
            'b.b.a' => 100,
            'b.b.b' => 200
        ];
        $overAbsent = $absent;
        unset($overAbsent['b']);

        $providers = self::getConfigProvidersLabeled($initial, $over);

        $ret->append(new \ArrayIterator([
            'hierarchy/over-flat' => [
                $providers['hierarchy']->data[0]->get(),
                $overFlat,
                $overAbsent,
                $flat
            ],
            'child/over-flat' => [
                $providers['childs']->data[0]->get(),
                $overFlat,
                $overAbsent,
                $flat
            ]
        ]));

        // ====================================================================
        $providers = self::getConfigProvidersLabeled($over, $initial);
        $overClear = $over;
        $overClear['a.b'] = $over['a']['b'];
        $ret->append(new \ArrayIterator([
            'hierarchy/flat-over' => [
                $providers['hierarchy']->data[0]->get(),
                $overFlat,
                $overAbsent,
                $overClear
            ],
            'flat/over;child' => [
                $providers['childs']->data[0]->get(),
                $overFlat,
                $overAbsent,
                $overClear
            ]
        ]));

        // ====================================================================
        return $ret;
    }

    private function assertArrayEquals(array $a, array $b)
    {
        $this->assertTrue(Iterables::valuesEquals($a, $b), sprintf("Expect\n%s but have\n%s", print_r($a, true), print_r($b, true)));
    }

    #[DataProvider('configProvider')]
    public function testRead(Configuration $baseConfig, $flatResult, $absent): void
    {
        $interpolator = $baseConfig->getInterpolator();

        $clone = clone $baseConfig;
        $configs = [
            $baseConfig,
            $baseConfig->copy(),
            $baseConfig->copy($baseConfig->getInterpolator()),
            $clone
        ];

        foreach ($configs as $config) {
            $toArray = $config->toArray();

            $this->assertSame($interpolator, $config->getInterpolator());
            // Values
            foreach ($flatResult as $k => $v) {

                // Presence
                $this->assertTrue($config->isPresent($k));
                $opt = $config->getOptional($k);
                $this->assertTrue($opt->isPresent());

                // Value
                $this->assertSame($v, $opt->get());
                $this->assertSame($v, $config[$k]);
            }
            $this->assertEquals($flatResult, $toArray);

            // Absence
            foreach ($absent as $k => $v) {
                $this->assertFalse($config->isPresent($k));
                $opt = $config->getOptional($k);
                $this->assertFalse($opt->isPresent());
            }
            // Keys
            $flatKeys = \array_keys($flatResult);

            $this->assertArrayEquals($flatKeys, \array_keys($toArray));
            $this->assertArrayEquals($flatKeys, iterator_to_array(Iterables::keys($config)));
        }

        // Clear
        $cleared = $clone;
        $cleared->clear();
        $clearedToArray = $cleared->toArray();

        $empty = Configurations::emptyTreeCopyOf($config);
        $this->assertSame(0, \count($empty));

        foreach (
            [
                $cleared,
                $empty
            ] as $empty
        )
            $this->assertSame($interpolator, $empty->getInterpolator());

        if ($expectClear = $args['clear'] ?? []) {
            $this->assertArrayEquals($expectClear, $clearedToArray);
            $this->assertSame(\count($expectClear), \count($cleared));
        }
    }

    // // ========================================================================
    public static function subConfigProvider(): iterable
    {
        $ret = new \AppendIterator();
        $aconfig = [
            'a.a' => 1,
            'a.b' => 2
        ];
        $bconfig = [
            'b.a.a' => 10,
            'b.a.b' => 11,
            'b.b' => 20
        ];
        $sub = fn($nullResult) => [
            [null, $nullResult],
            [
                'a',
                [
                    'a' => 1,
                    'b' => 2
                ]
            ],
            [
                'b',
                [
                    'a.a' => 10,
                    'a.b' => 11,
                    'b' => 20
                ]
            ],
            [
                'b.a',
                [
                    'a' => 10,
                    'b' => 11
                ]
            ]
        ];
        $ab = \array_merge($aconfig, $bconfig);
        $merger = Provided::merge(
            self::getConfigProviders($aconfig, $bconfig),
            [new Provided('ab', [$sub($ab)])]
        );
        $ret->append($merger);

        $ba = \array_merge($bconfig, $aconfig);
        $merger = Provided::merge(
            self::getConfigProviders($bconfig, $aconfig),
            [new Provided('ba', [$sub($ba)])]
        );
        $ret->append($merger);
        return $ret;
    }

    #[Test]
    #[DataProvider('subConfigProvider')]
    public function subTreeCopy(Configuration $config, array $sub): void
    {
        foreach ($sub as [$k, $subResult]) {
            $subConfig = $config->subTreeCopy($k);
            // A Configuration contents is order independant, so 'equals' for comparisons
            $this->assertEquals($subResult, $subConfig->toArray());
        }
    }

    #[Test]
    #[DataProvider('configurationsProvider')]
    public function copyBranches(Configuration $config): void
    {
        $treea = [
            'a' => [
                'aa' => 'AA',
                'ab' => 'AB',
            ]
        ];
        $tree = [
            ...$treea,
            'b' => 'B',
        ];
        $config->mergeTree($tree);

        $cpy = $config->copyBranches('a');
        $this->assertSame(2, \count($cpy));
        $this->assertSame('AA', $cpy['a.aa']);
        $this->assertSame('AB', $cpy['a.ab']);
        $this->assertNull($cpy['b']);

        $cpy = $config->copyBranches('b');
        $this->assertSame(1, \count($cpy));
        $this->assertNull($cpy['a.aa']);
        $this->assertNull($cpy['a.ab']);
        $this->assertSame('B', $cpy['b']);

        $cpy = $config->copyBranches('a', 'b');
        $this->assertSame(3, \count($cpy));
        $this->assertSame('AA', $cpy['a.aa']);
        $this->assertSame('AB', $cpy['a.ab']);
        $this->assertSame('B', $cpy['b']);
    }

    // // ========================================================================
    public static function selectProvider(): iterable
    {
        $aconfig = [
            'a.a' => 1,
            'a.b' => 2
        ];
        $bconfig = [
            'b.a.a' => 10,
            'b.a.b' => 11,
            'b.b' => 20
        ];
        $baresult = [
            'b.a.a' => 10,
            'b.a.b' => 11
        ];
        $sub = fn($nullResult) => [
            [null, $nullResult],
            ['a', $aconfig],
            ['b', $bconfig],
            ['b.a', $baresult]
        ];
        $ret = new \AppendIterator();
        $ab = \array_merge($aconfig, $bconfig);
        $ba = \array_merge($bconfig, $aconfig);

        $ret->append(Provided::merge(
            self::getConfigProviders($aconfig, $bconfig),
            [new Provided('ab', [$sub($ab)])]
        ));
        $ret->append(Provided::merge(
            self::getConfigProviders($bconfig, $aconfig),
            [new Provided('ba', [$sub($ba)])]
        ));
        return $ret;
    }

    #[Test]
    #[DataProvider('selectProvider')]
    public function select(Configuration $config, array $sub): void
    {
        foreach ($sub as [$k, $subResult]) {
            $subConfig = $config->copyBranches($k);
            // A Configuration contents is order independant, so 'equals' for comparisons
            $this->assertEquals($subResult, $subConfig->toArray(), "select $k");
        }
    }

    public static function configurationsProvider(): \Generator
    {
        return Provided::merge(self::getConfigProviders([]));
    }

    #[DataProvider('configurationsProvider')]
    public function testSubTreeView(Configuration $config): void
    {
        $view = $config->subTreeView('a');

        $this->assertFalse($config->isPresent('a'));
        $this->assertTrue($config->nodeIsPresent('a'));

        $config['a.aa'] = 'aa';
        $this->assertSame('aa', $view['aa']);

        // Remove the root of the view
        $config->removeNode('a');
        $this->assertSame(0, \count($config));
        $this->assertSame('aa', $view['aa']);
    }

    public function testSubTreeViewFix1(): void
    {
        $tree = [
            'a' => [
                'aa' => 1,
                'ab' => 2,
            ]
        ];
        $config = Configurations::ofTree($tree);
        $view = $config->subTreeView('a');
        $this->assertSame($tree['a'], $view->toArray());

        $config['a.aa'] = 5;
        $this->assertSame(5, $view['aa']);
    }

    #[Test]
    public function creationCount(): void
    {
        $tree = [
            'a' => [
                'aa' => 1,
                'ab' => 2,
            ],
            'b' => 1,
        ];
        $configs = [];
        $config = Configurations::ofTree($tree);
        $unmod = Configurations::unmodifiable($config);
        $cpy = Configurations::treeCopyOf($config);
        $configs = [
            'conf' => &$config,
            'unmod' => &$unmod,
            'cpy' => &$cpy
        ];
        $check = function (int $cnt, string $testLabel) use ($configs): void {

            foreach ($configs as $label => $c) {
                Assert::assertSame($cnt, \count($c), "$label ($testLabel)");
            }
        };
        //Init
        $check(3, 'init');
        Assert::assertSame(2, \count($config->subTreeCopy('a')));
        Assert::assertSame(3, \count($config->copyBranches('a', 'b')));

        // Add
        $config['c'] = 2;
        $cpy = Configurations::treeCopyOf($config);
        $check(4, 'add');
        Assert::assertSame(3, \count($config->copyBranches('a', 'b')));
        // Update
        $config['b'] = 3;
        $cpy = Configurations::treeCopyOf($config);
        $check(4, 'update');
        // Unset leaf
        unset($config['b']);
        $cpy = Configurations::treeCopyOf($config);
        $check(3, 'unset leaf');
        Assert::assertSame(2, \count($config->copyBranches('a', 'b')));
        // Unset Node
        $config->removeNode('a');
        $cpy = Configurations::treeCopyOf($config);
        $check(1, 'unset node');
        Assert::assertSame(0, \count($config->subTreeCopy('a')));
    }

    #[Test]
    public function subTreeViewCount(): void
    {
        $tree = [
            'a' => [
                'aa' => 1,
                'ab' => ['aba' => 1],
            ],
            'b' => 1,
        ];
        $config = Configurations::ofTree($tree);
        $view = $config->subTreeView('a');

        $this->assertSame(3, \count($config));
        $this->assertSame(2, \count($view));

        $config['a.ac'] = 3;
        $this->assertSame(4, \count($config));
        $this->assertSame(3, \count($view));

        unset($config['a.ac']);
        $this->assertSame(3, \count($config));
        $this->assertSame(2, \count($view));

        $config->removeNode('a.ab');
        $this->assertSame(2, \count($config));
        $this->assertSame(1, \count($view));

        // Remove the view root
        $config->removeNode('a');
        $this->assertSame(1, \count($config));
        $this->assertSame(1, \count($view));
    }


    #[Test]
    public function subTreeViewUnmodifiable(): void
    {
        $tree = [
            'a' => [
                'aa' => 1,
                'ab' => 2,
            ],
        ];
        $config = Configurations::ofTree($tree);
        $view = $config->subTreeView('a');

        $this->expectException(UnmodifiableException::class);
        $view['ac'] = 3;
    }
}
