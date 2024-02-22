<?php
declare(strict_types = 1);
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Config\Configurations;
use Time2Split\Help\Arrays;
use Time2Split\Help\Traversables;

final class ConfigurationTest extends TestCase
{

    public static function getConfigProviders(array ...$configs): array
    {
        return [
            'simple' => fn () => Configurations::ofRecursive(\array_merge_recursive(...$configs)),
            'childs' => function () use ($configs) {
                $configs = \array_reverse($configs);

                $config = Configurations::ofRecursive(\array_pop($configs));

                while (! empty($configs)) {
                    $config = Configurations::emptyChild($config);
                    $config->merge(Configurations::ofRecursive(\array_pop($configs)));
                }
                return $config;
            },
            'hierarchy' => fn () => Configurations::hierarchy(...\array_map(Configurations::ofRecursive(...), $configs))
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

        $copy = Configurations::of($baseConfig);
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
            $this->assertArrayEquals($flatKeys, Configurations::getKeysOf($config));
        }

        // Clear
        $cleared = $clone;
        $cleared->clear();
        $clearedToArray = $cleared->toArray();

        $empty = Configurations::emptyOf($config);
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
}