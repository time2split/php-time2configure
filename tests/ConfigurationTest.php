<?php
declare(strict_types = 1);
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Config\Configurations;
use Time2Split\Help\Arrays;
use Time2Split\Help\Traversables;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class ConfigurationTest extends TestCase
{

    public static function getConfigProviders(array ...$configs): array
    {
        $mconfigs = \array_merge_recursive(...$configs);
        return [
            'simple' => fn () => Configurations::ofTree($mconfigs),
            'builder' => fn () => Configurations::builder()->mergeTree($mconfigs),
            'childs' => function () use ($configs) {
                $configs = \array_reverse($configs);

                $config = Configurations::ofTree(\array_pop($configs));

                while (! empty($configs)) {
                    $config = Configurations::emptyChild($config);
                    $config->merge(Configurations::ofTree(\array_pop($configs)));
                }
                return $config;
            },
            'hierarchy' => fn () => Configurations::hierarchy(...\array_map(Configurations::ofTree(...), $configs))
        ];
    }

    public static function configProvider(): array
    {
        $ret = [];

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

        $providers = self::getConfigProviders($initial);

        foreach ($providers as $provider)
            $ret[] = [

                [
                    'provide' => $provider,
                    'flat' => $flat,
                    'absent' => $absent
                ]
            ];
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

        $providers = self::getConfigProviders($initial, $over);
        $ret['over/flat;hierarchy'] = [
            [
                'provide' => $providers['hierarchy'],
                'flat' => $overFlat,
                'absent' => $overAbsent,
                'clear' => $flat
            ]
        ];
        $ret['over/flat;child'] = [
            [
                'provide' => $providers['childs'],
                'flat' => $overFlat,
                'absent' => $overAbsent,
                'clear' => $flat
            ]
        ];

        // ====================================================================
        $providers = self::getConfigProviders($over, $initial);
        $overClear = $over;
        $overClear['a.b'] = $over['a']['b'];
        unset($overClear['a']);
        $ret['flat/over:hierarchy'] = [
            [
                'provide' => $providers['hierarchy'],
                'flat' => $overFlat,
                'absent' => $overAbsent,
                'clear' => $overClear
            ]
        ];
        $ret['flat/over;child'] = [
            [
                'provide' => $providers['childs'],
                'flat' => $overFlat,
                'absent' => $overAbsent,
                'clear' => $overClear
            ]
        ];

        // ====================================================================
        return $ret;
    }

    private function assertArrayEquals(array $a, array $b)
    {
        $this->assertTrue(Arrays::contentEquals($a, $b), sprintf("Expect\n%s but have\n%s", print_r($a, true), print_r($b, true)));
    }

    #[DataProvider('configProvider')]
    public function testRead(array $args): void
    {
        [
            'provide' => $provide,
            'flat' => $flatResult,
            'absent' => $absent
        ] = $args;

        $baseConfig = $provide();
        $interpolator = $baseConfig->getInterpolator();

        $copy = Configurations::copyOf($baseConfig);
        $clone = clone $baseConfig;
        $configs = [
            $baseConfig,
            $copy,
            $clone
        ];

        foreach ($configs as $config) {
            $toArray = $config->toArray();
            $null = \array_fill(0, \count($toArray), null);

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
            $this->assertArrayEquals($flatKeys, iterator_to_array(Traversables::keys($config)));
        }

        // Clear
        $cleared = $clone;
        $cleared->clear();
        $clearedToArray = $cleared->toArray();

        $empty = Configurations::emptyCopyOf($config);
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

    // ========================================================================
    public static function subConfigProvider(): array
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
        $sub = fn ($nullResult) => [
            [
                null,
                $nullResult
            ],
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
        $ret['a/b'] = [
            [
                'configs' => [
                    $aconfig,
                    $bconfig
                ],
                'sub' => $sub(\array_merge($aconfig, $bconfig))
            ]
        ];
        $ret['b/a'] = [
            [
                'configs' => [
                    $bconfig,
                    $aconfig
                ],
                'sub' => $sub(\array_merge($bconfig, $aconfig))
            ]
        ];
        return $ret;
    }

    #[DataProvider('subConfigProvider')]
    public function testSubTreeCopy(array $args): void
    {
        $configs = $args['configs'];
        $sub = $args['sub'];
        $providers = self::getConfigProviders(...$configs);

        foreach ($providers as $provider) {
            $config = $provider();

            foreach ($sub as [
                $k,
                $subResult
            ]) {
                $subConfig = $config->subTreeCopy($k);
                $this->assertSame($subResult, $subConfig->toArray());
            }
        }
    }

    // ========================================================================
    public static function selectProvider(): array
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
            [
                null,
                $nullResult
            ],
            [
                'a',
                $aconfig
            ],
            [
                'b',
                $bconfig
            ],
            [
                'b.a',
                $baresult
            ]
        ];
        $ret['a/b'] = [
            /* 'configs' => */
            [
                $aconfig,
                $bconfig
            ],
            /*'sub' =>*/
            $sub(\array_merge($aconfig, $bconfig))
        ];
        $ret['b/a'] = [
            /* 'configs' => */
            [
                $bconfig,
                $aconfig
            ],
            /*'sub' =>*/
            $sub(\array_merge($bconfig, $aconfig))
        ];
        return $ret;
    }

    #[DataProvider('selectProvider')]
    public function testSelect(array $configs, array $sub): void
    {
        $providers = self::getConfigProviders(...$configs);

        foreach ($providers as $provider) {
            $config = $provider();

            foreach ($sub as [
                $k,
                $subResult
            ]) {
                $subConfig = $config->copyBranches($k);
                $this->assertSame($subResult, $subConfig->toArray(), "select $k");
            }
        }
    }

    public static function subTreeViewProvider(): \Generator
    {
        return (function () {
            foreach (self::getConfigProviders([]) as $k => $provider)
                yield $k => [
                    $provider
                ];
        })();
    }

    #[DataProvider('subTreeViewProvider')]
    public function testSubTreeView(\Closure $provider): void
    {
        $config = $provider();

        $view = $config->subTreeView('a');

        $this->assertFalse($config->isPresent('a'));
        $this->assertTrue($config->nodeIsPresent('a'));

        $view['b'] = 0;

        $this->assertSame(0, $config['a.b']);
    }
}