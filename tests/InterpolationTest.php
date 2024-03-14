<?php
declare(strict_types = 1);
namespace Time2Split\Config\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Config\Configuration;
use Time2Split\Config\Configurations;
use Time2Split\Config\Interpolation;
use Time2Split\Config\Interpolator;
use Time2Split\Config\Interpolators;
use Time2Split\Config\Tests\Help\Producer;
use Time2Split\Config\Tests\Help\Provided;
use Time2Split\Config\_private\Decorator\ResetInterpolationDecorator;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class InterpolationTest extends TestCase
{

    public function testRecursive(): void
    {
        $interpolator = Interpolators::recursive();
        $config = Configurations::builder()->setInterpolator($interpolator)->build();

        $val = 15;
        $config['a'] = $val;

        // Check one interpolated value
        $compilation = $interpolator->compile('${a}');
        $this->assertTrue($compilation->isPresent());
        $this->assertSame($val, $interpolator->execute($compilation->get(), $config));

        // Check one config interpolated value
        $config['b'] = '${a}';
        $this->assertSame($val, $config['b']);

        // Check one config interpolated text value
        $config['pref'] = 'A';
        $config['suff'] = 'C';
        $config['text'] = '${pref}B${suff}';
        $this->assertSame('ABC', $config['text']);

        // Check all values
        $expect = [
            'a' => $val,
            'b' => $val,
            'pref' => 'A',
            'suff' => 'C',
            'text' => 'ABC'
        ];
        $this->assertSame($expect, $config->toArray());
        $c = 0;

        foreach ($config as $k => $v) {
            $this->assertSame($expect[$k], $v);
            $c ++;
        }
        $this->assertSame(\count($expect), $c, 'count');

        // Reset interpolator (null)
        $rawConfig = $config->copy(Interpolators::null());
        $expect = [
            'a' => $val,
            'b' => '${a}',
            'pref' => 'A',
            'suff' => 'C',
            'text' => '${pref}B${suff}'
        ];
        $this->assertSame($expect, $rawConfig->toArray(), 'raw:toArray()');
        $c = 0;

        foreach ($rawConfig as $k => $v) {
            $this->assertSame($expect[$k], $v, 'raw:get');
            $c ++;
        }
        $this->assertSame(\count($expect), $c, 'raw:count');
    }

    private static function configProvider(Interpolator $intp, array $tree): array
    {
        $configBase = fn () => Configurations::builder()->setInterpolator($intp)->mergeTree($tree);

        $ret[] = new Provided('tree', [
            new Producer($configBase)
        ]);
        $ret[] = new Provided('hierarchy', [
            new Producer(fn () => Configurations::emptyChild($configBase()))
        ]);
        return $ret;
    }

    private const readTree = [
        'a' => 10,
        'b' => '${a}'
    ];

    public static function _testCopy(): iterable
    {
        return Provided::merge(self::configProvider(Interpolators::recursive(), self::readTree));
    }

    #[DataProvider('_testCopy')]
    public function testCopy(Configuration $configBase)
    {
        $configCopy = $configBase->copy();
        $this->assertTrue($configBase->getOptional('b', false)
            ->get() instanceof Interpolation, 'base is Interpolation');
        $this->assertFalse($configCopy->getOptional('b', false)
            ->get() instanceof Interpolation, 'copy is not Interpolation');
    }

    #[DataProvider('_testCopy')]
    public function testRawCopy(Configuration $configBase)
    {
        $configCopy = $configBase->rawCopy();
        $this->assertTrue($configBase->getOptional('b', false)
            ->get() instanceof Interpolation, 'base is Interpolation');
        $this->assertTrue($configCopy->getOptional('b', false)
            ->get() instanceof Interpolation, 'copy is not Interpolation');
    }

    public function testBuilderSetInterpolator()
    {
        $val = 10;
        $text = '${a}';
        $builder = Configurations::builder()->merge(self::readTree);

        $this->assertSame($text, $builder['b'], 'null');
        $builder->setInterpolator(Interpolators::recursive());
        $this->assertSame($val, $builder['b'], 'recursive');
        $builder->setInterpolator();
        $this->assertSame($text, $builder['b'], 'reset null');
    }

    #[DataProvider('_testCopy')]
    public function testResetInterpolationDecorator(Configuration $configBase)
    {
        $base = self::readTree;
        $resetIntp = new class($configBase, Interpolators::null()) extends ResetInterpolationDecorator {};

        foreach ($base as $k => $v) {
            $opt = $resetIntp->getOptional($k);
            $this->assertSame($v, $resetIntp[$k]);
            $this->assertTrue($opt->isPresent());
            $this->assertSame($v, $opt->get());
        }
        $this->assertSame($base, $resetIntp->toArray());
    }
}