<?php

declare(strict_types=1);

namespace Time2Split\Config;

use Time2Split\Config\Entry\Consumer;
use Time2Split\Config\Entry\MapKey;
use Time2Split\Config\Entry\MapValue;
use Time2Split\Config\_private\Entry\AMapKey;
use Time2Split\Config\_private\Entry\AMapKeyValue;
use Time2Split\Config\_private\Entry\AMapValue;
use Time2Split\Config\_private\Entry\AbstractConsumer;
use Time2Split\Help\Classes\NotInstanciable;
use Time2Split\Config\Entry\ReadingMode;
use Time2Split\Help\Iterables;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class Entries
{
    use NotInstanciable;

    // ========================================================================
    // ENTRY VALUE READING
    // ========================================================================

    /**
     * Get the interpolation of an {@link Interpolation} value, or the value itself if not an {@link Interpolation}.
     *
     * @template V
     * 
     * @param Interpolation<V>|V $rawValue
     *            A raw value to interpolate.
     * @param Configuration<mixed,V> $config
     *            The configuration where the value belongs to.
     * @return V The interpolated value.
     *
     * @see Interpolation
     */
    public static function interpolatedValueOf($rawValue, Configuration $config): mixed
    {
        if (!($rawValue instanceof Interpolation))
            return $rawValue;

        return $config->getInterpolator()->execute($rawValue->compilation, $config);
    }

    /**
     * Get the base value of an interpolation value, or the value itself if not an interpolation.
     *
     * @template V
     * 
     * @param Interpolation<V>|V $rawValue
     *            A raw value.
     * @return V The base value.
     *
     * @see Interpolation
     */
    public static function baseValueOf($rawValue): mixed
    {
        return $rawValue instanceof Interpolation ? $rawValue->baseValue : $rawValue;
    }

    /**
     * Get a value from a raw value according to a reading mode.
     * 
     * @template K
     * @template V
     * 
     * @param V $rawValue
     *            A raw value to interpolate.
     * @param Configuration<K,V> $config
     *            The configuration where the value belongs to.
     * @param ReadingMode $mode
     *            The reading mode to use.
     * @return V|Interpolation<V> The value.
     *
     * @see Entries::interpolatedValueOf
     * @see Entries::baseValueOf
     * @see ReadingMode
     * @see Interpolation
     */
    public static function valueOf($rawValue, Configuration $config, ReadingMode $mode = ReadingMode::Normal): mixed
    {
        return match ($mode) {
            ReadingMode::Interpolate => self::interpolatedValueOf($rawValue, $config),
            ReadingMode::BaseValue => self::baseValueOf($rawValue),
            ReadingMode::RawValue => $rawValue
        };
    }

    // TRAVERSABLES

    /**
     * Get a sequence of interpolated values from a sequence of raw entries; keys are preserved.
     *
     * @template K
     * @template V
     * 
     * @param iterable<V|Interpolation<V>> $rawEntries
     *            A sequence of raw values.
     * @param Configuration<K,V> $config
     *            The configuration where the values belongs to.
     * @return \Iterator<V|Interpolation<V>> The sequence of interpolated values.
     */
    public static function interpolatedEntriesOf(iterable $rawEntries, Configuration $config): \Iterator
    {
        foreach ($rawEntries as $k => $v)
            yield $k => self::interpolatedValueOf($v, $config);
    }

    /**
     * Get a sequence of base values from a sequence of raw entries; keys are preserved.
     *
     * @template V
     * 
     * @param iterable<V|Interpolation<V>> $rawEntries
     *            A sequence of raw values.
     * @return \Iterator<V> The sequence of base values.
     */
    public static function baseEntriesOf(iterable $rawEntries): \Iterator
    {
        foreach ($rawEntries as $k => $v)
            yield $k => self::baseValueOf($v);
    }

    /**
     * Get a sequence of values from a sequence of raw entries according to a {@link ReadingMode}; keys are preserved.
     *
     * @template K
     * @template V
     * 
     * @param \Iterator<K,V|Interpolation<V>> $rawEntries
     *            A sequence of raw values.
     * @param Configuration<K,V> $config
     *            The configuration where the values belongs to.
     * @param ReadingMode $mode
     *            The reading mode to use.
     * @return \Iterator<K,V|Interpolation<V>> The sequence of values.
     *
     * @see Entries::interpolatedEntriesOf
     * @see Entries::baseEntriesOf
     * @see ReadingMode
     * @see Interpolation
     */
    public static function entriesOf(\Iterator $rawEntries, Configuration $config, ReadingMode $mode = ReadingMode::Normal): \Iterator
    {
        return match ($mode) {
            ReadingMode::Interpolate => self::interpolatedEntriesOf($rawEntries, $config),
            ReadingMode::BaseValue => self::baseEntriesOf($rawEntries),
            ReadingMode::RawValue => $rawEntries
        };
    }

    // ========================================================================
    // MAP FROM CLOSURES
    // ========================================================================
    public static function mapKey(\Closure $map): MapKey
    {
        return new class($map) extends AMapKey
        {

            public function map(Configuration $config, $key, $value): Entry
            {
                return new Entry(($this->map)($key, $config), $value);
            }
        };
    }

    public static function mapKeyFromValue(\Closure $map): MapKey
    {
        return new class($map) extends AMapKey
        {

            public function map(Configuration $config, $key, $value): Entry
            {
                return new Entry(($this->map)($value, $key, $config), $value);
            }
        };
    }

    public static function mapValue(\Closure $map): MapValue
    {
        return new class($map) extends AMapValue
        {

            public function map(Configuration $config, $key, $value): Entry
            {
                return new Entry($key, ($this->map)($value, $key, $config));
            }
        };
    }

    public static function mapValueFromKey(\Closure $map): MapValue
    {
        return new class($map) extends AMapValue
        {

            public function map(Configuration $config, $key, $value): Entry
            {
                return new Entry($key, ($this->map)($key, $config));
            }
        };
    }

    public static function mapEntry(\Closure $map): MapKey&MapValue
    {
        return new class($map) extends AMapKeyValue
        {

            public function map(Configuration $config, $key, $value): Entry
            {
                return ($this->map)($key, $value, $config);
            }
        };
    }

    // ========================================================================
    // CONSUMMER
    // ========================================================================
    public static function consumeEntry(\Closure $consumer): Consumer
    {
        return new class($consumer) extends AbstractConsumer
        {

            public function consume(Configuration $config, $key, $value): void
            {
                ($this->consumer)($key, $value, $config);
            }
        };
    }
}
