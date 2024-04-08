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
use Time2Split\Config\Entry\Map;
use Time2Split\Help\Classes\NotInstanciable;
use Time2Split\Config\Entry\ReadingMode;

/**
 * Functions on Entry.
 * 
 * @author Olivier Rodriguez (zuri)
 *
 * @package time2configure\configuration
 */
final class Entries
{
    use NotInstanciable;

    // ========================================================================
    // ENTRY VALUE READING
    // ========================================================================

    /**
     * Gets the interpolation of an Interpolation value, or the value itself if not an interpolation.
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
     * Gets the base value of an interpolation value, or the value itself if not an interpolation.
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
     * Gets a value from a raw value according to a reading mode.
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
     * Maps each entry to get its interpolated value.
     *
     * @template K
     * @template V
     * 
     * @param iterable<K,V|Interpolation<V>> $rawEntries
     *            A sequence of raw entries.
     * @param Configuration<K,V> $config
     *            The configuration where the values belongs to.
     * @return \Iterator<K,V|Interpolation<V>> The sequence of interpolated values.
     */
    public static function interpolatedEntriesOf(iterable $rawEntries, Configuration $config): \Iterator
    {
        foreach ($rawEntries as $k => $v)
            yield $k => self::interpolatedValueOf($v, $config);
    }

    /**
     * Maps each entry to get its base value.
     *
     * @template K
     * @template V
     * 
     * @param iterable<K,V|Interpolation<V>> $rawEntries
     *            A sequence of raw entries.
     * @return \Iterator<K,V> The sequence of base values.
     * 
     * @see Interpolation
     */
    public static function baseEntriesOf(iterable $rawEntries): \Iterator
    {
        foreach ($rawEntries as $k => $v)
            yield $k => self::baseValueOf($v);
    }

    /**
     * Maps each entry to get its base|raw|interpolated value according to a reading mode.
     *
     * @template K
     * @template V
     * 
     * @param \Iterator<K,V|Interpolation<V>> $rawEntries
     *            A sequence of raw entries.
     * @param Configuration<K,V> $config
     *            The configuration where the values belongs to.
     * @param ReadingMode $mode
     *            The reading mode to use.
     * @return \Iterator<K,V|Interpolation<V>> The sequence of values.
     *
     * @see ReadingMode
     * @see Interpolation
     * @package time2configure\configuration
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

    /**
     * Transforms a value mapping to a consumer.
     * 
     * The Entry returned by the initial mapping is then unused.
     * 
     * @template K
     * @template V
     * @template MK
     * @template MV
     * @param Map<K,V,MK,MV> $map An entry mapper.
     * @return Consumer<K,V>
     */
    public static function mapAsConsumer(Map $map): Consumer
    {
        return new class($map) implements Consumer
        {
            public function __construct(private readonly Map $map)
            {
            }

            public function consume(Configuration $config, $key, $value): void
            {
                $this->map->map($config, $key, $value);
            }
        };
    }

    /**
     * Constructs a MapKey from a key mapping closure.
     * 
     * @param \Closure $map
     *  - $consumer(K $key, Configuration<K,V>): Entry
     * @return MapKey<mixed,mixed,mixed> A key mapping.
     */
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

    /**
     * Constructs a MapKey from a value mapping closure.
     * 
     * @param \Closure $map
     *  - $consumer(V $value, K $key, Configuration<K,V>): Entry
     * @return MapKey<mixed,mixed,mixed> A key mapping.
     */
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

    /**
     * Constructs a MapValue from a value mapping closure.
     * 
     * @param \Closure $map
     *  - $consumer(V $value, K $key, Configuration<K,V>): Entry
     * @return MapValue<mixed,mixed,mixed> A value mapping.
     */
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

    /**
     * Constructs a MapValue from a key mapping closure.
     * 
     * @param \Closure $map
     *  - $consumer(K $key, Configuration<K,V>): Entry
     * @return MapValue<mixed,mixed,mixed> A value mapping.
     */
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

    /**
     * Constructs an entry mapping from an entry mapping closure.
     * 
     * @param \Closure $map
     *  - $consumer(K $key, V $value, Configuration<K,V>): Entry
     * @return MapKey&MapValue<mixed,mixed,mixed> A consumer.
     */
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

    /**
     * Constructs a consumer from a closure.
     * 
     * @param \Closure $consumer
     *  - $consumer(Configuration<K,V>, K $key, V $value):void
     * @return Consumer<mixed,mixed> A consumer.
     */
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
