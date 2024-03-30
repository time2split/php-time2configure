<?php

namespace Time2Split\Config;

use Time2Split\Help\Optional;
use Time2Split\Config\Entry\ReadingMode;

/**
 * A set of accessible and modifiable key => value entries.
 * This interface does not describe the format and the semantic of the key part,
 * it may be as simple of php array keys, or be more complex like a hierarchical structure "a.b.c".
 *
 * @template K
 * @template V
 * @extends \ArrayAccess<K,V>
 * @extends \IteratorAggregate<K,V>
 * 
 * @author Olivier Rodriguez (zuri)
 */
interface BaseConfiguration extends \ArrayAccess, \IteratorAggregate, \Countable
{

    /**
     * Get the interpolator used to automatically interpolate the values.
     *
     * @return Interpolator The configuration interpolator.
     */
    public function getInterpolator(): Interpolator;

    // ========================================================================

    /**
     * Get an iterator of the entries in the selected reading mode.
     * 
     * @param ReadingMode $mode
     *            The mode with which to retrieves the entry value.
     * @return \Iterator<K,V>
     * 
     * @see ReadingMode
     */
    public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Iterator;

    /**
     * Retrieves the value of an entry.
     * 
     * @param mixed $offset The key of the entry to retrieves.
     * @param ReadingMode $mode
     *            The mode with which to retrieves the entry value.
     * @return V The value of the offset, or null if absent.
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($offset, ReadingMode $mode = ReadingMode::Normal): mixed;

    /**
     * Retrieves optionally the value of an entry.
     *
     * @param mixed $offset
     *            The offset to retrieve.
     * @param ReadingMode $mode
     *            The mode with which to retrieves the entry value.
     * @return Optional<V> An optional of the value to retrieve.
     * 
     * @see https://time2split.github.io/php-time2help/classes/Time2Split-Help-Optional.html Optional
     */
    public function getOptional($offset, ReadingMode $mode = ReadingMode::Normal): Optional;

    /**
     * Whether an offset is present with a value (the value may be null).
     *
     * @param mixed $offset
     *            An offset to check for.
     * @return bool Returns true on success or false on failure.
     */
    public function isPresent($offset): bool;

    /**
     * Drop all items from the configuration.
     */
    public function clear(): void;

    /**
     * Make a copy of the configuration.
     *
     * @param Interpolator $interpolator
     *            If not set (ie. null) the copy will contains the interpolated value of the configuration tree.
     *            If set the copy will use this interpolator on the raw base value to create a new interpolated configuration.
     *            Note that the interpolator may be the same as $config, in that case it means that the base interpolation is conserved.
     * @return static A new Configuration instance.
     */
    public function copy(?Interpolator $interpolator = null): static;
}
