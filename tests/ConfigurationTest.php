<?php
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Config\Configurations;
use Time2Split\Help\Arrays;

final class ConfigurationTest extends TestCase
{

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
        $ret[] = [
            [
                'provide' => fn () => Configurations::ofRecursive($initial),
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
        $ret[] = [
            [
                'provide' => function () use ($initial, $over) {
                    $config = Configurations::ofRecursive($initial);
                    $config = Configurations::emptyChild($config);
                    $config->merge(Configurations::ofRecursive($over));

                    return $config;
                },
                'flat' => $overFlat,
                'absent' => $overAbsent,
                'clear' => $flat
            ]
        ];
        // ====================================================================
        $ret[] = [
            [
                'provide' => fn () => Configurations::hierarchy(Configurations::ofRecursive($initial), Configurations::ofRecursive($over)),
                'flat' => $overFlat,
                'absent' => $overAbsent,
                'clear' => $flat
            ]
        ];
        // ====================================================================
        return $ret;
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

            $this->assertTrue(Arrays::contentEquals($flatKeys, \array_keys($toArray)));
            $this->assertTrue(Arrays::contentEquals($flatKeys, $config->keys()));
            $this->assertTrue(Arrays::contentEquals($flatKeys, \iterator_to_array($config->traversableKeys())));
        }

        // Clear
        $cleared = $clone;
        $cleared->clear();

        foreach ([
            $cleared,
            Configurations::emptyOf($config)
        ] as $empty)
            $this->assertSame($interpolator, $empty->getInterpolator());

        if ($expectClear = $args['clear'] ?? [])
            $this->assertTrue(Arrays::contentEquals($expectClear, $cleared->toArray()));
    }
}