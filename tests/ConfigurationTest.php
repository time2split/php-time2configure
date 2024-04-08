<?php

declare(strict_types=1);

namespace Time2Split\Config\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Config\Configuration;
use Time2Split\Config\Configurations;
use Time2Split\Help\Arrays;
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
            'simple' => new Provided('simple', [new Producer(fn () => Configurations::ofTree($mconfigs))]),
            'builder' => new Provided('builder', [new Producer(fn () => Configurations::builder()->mergeTree($mconfigs))]),
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
            ]), 'hierarchy' => new Provided('hierarchy', [
                new Producer(fn () => Configurations::hierarchy(...\array_map(Configurations::ofTree(...), $configs)))
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

        foreach ([
            $cleared,
            $empty
        ] as $empty)
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
        $sub = fn ($nullResult) => [
            [null, $nullResult],
            [
                'a', [
                    'a' => 1,
                    'b' => 2
                ]
            ],
            [
                'b',  [
                    'a.a' => 10,
                    'a.b' => 11,
                    'b' => 20
                ]
            ],
            [
                'b.a', [
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

    #[DataProvider('subConfigProvider')]
    public function testSubTreeCopy(Configuration $config, array $sub): void
    {
        foreach ($sub as [
            $k,
            $subResult
        ]) {
            $subConfig = $config->subTreeCopy($k);
            $this->assertSame($subResult, $subConfig->toArray());
        }
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
        $sub = fn ($nullResult) => [
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

    #[DataProvider('selectProvider')]
    public function testSelect(Configuration $config, array $sub): void
    {
        foreach ($sub as [
            $k,
            $subResult
        ]) {
            $subConfig = $config->copyBranches($k);
            $this->assertSame($subResult, $subConfig->toArray(), "select $k");
        }
    }

    public static function subTreeViewProvider(): \Generator
    {
        return Provided::merge(self::getConfigProviders([]));
    }

    #[DataProvider('subTreeViewProvider')]
    public function testSubTreeView(Configuration $config): void
    {
        $view = $config->subTreeView('a');

        $this->assertFalse($config->isPresent('a'));
        $this->assertTrue($config->nodeIsPresent('a'));

        $view['b'] = 0;
        unset($view);

        $this->assertSame(0, $config['a.b']);
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
}
