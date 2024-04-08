<?php

namespace Time2Split\Config;

use Time2Split\Help\Optional;
use Time2Split\Config\Entry\ReadingMode;

/**
 * A sequence of (K => V) entries with automatic interpolation features.
 * 
 * This is the base interface that must implement all configuration instances.
 * The library provides (for now) only {@see Configuration} implementations that may be
 * instanciated with the {@see Configurations} factory methods.
 * 
 * Note that this interface does not describe the format and the semantic of the key part,
 * it may be as simple of php array keys, or be more complex like a hierarchical structure "a.b.c".
 * 
 * @template K
 * @template V
 * @template I
 * @extends \ArrayAccess<K,V>
 * @extends \IteratorAggregate<K,V>
 * 
 * @author Olivier Rodriguez (zuri)
 * 
 * @see Interpolation
 * @package time2configure\configuration
 */
interface BaseConfiguration extends \ArrayAccess, \IteratorAggregate, \Countable
{

    /**
     * Gets the interpolator used.
     *
     * @return Interpolator<V,I> The configuration interpolator.
     */
    public function getInterpolator(): Interpolator;

    // ========================================================================

    /**
     * Gets an iterator over the entries in the selected reading mode.
     * 
     * @param ReadingMode $mode
     *            The mode with which to retrieves the entry value.
     * @return \Iterator<K,V>
     * 
     * @see ReadingMode
     */
    public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Iterator;

    // ========================================================================

    /**
     * Whether an offset exists.
     *
     * @param ?K $offset An offset to check for.
     * @return bool Returns true on success or false on failure.
     */
    public function offsetExists($offset): bool;

    /**
     * Retrieves the value of an entry.
     * 
     * @param ?K $offset The key of the entry to retrieve.
     * @param ReadingMode $mode
     *            The mode with which to retrieve the entry value.
     * @return V The value of the offset, or null if absent.
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($offset, ReadingMode $mode = ReadingMode::Normal): mixed;

    /**
     * Assigns a value to the specified offset.
     * 
     * @param ?K $offset The offset to assign the value to.
     * @param V $value The value to set.
     */
    public function offsetSet($offset, $value): void;

    /**
     * Unsets an offset
     *
     * @param ?K $offset The offset to unset.
     */
    public function offsetUnset($offset): void;

    // ========================================================================

    /**
     * Retrieves the value of an entry in an Optional.
     *
     * @param ?K $offset
     *            The offset to retrieve.
     * @param ReadingMode $mode
     *            The mode with which to retrieves the entry value.
     * @return Optional<V> An optional of the value to retrieve, or an
     * empty optional if the offset is absent.
     * 
     * @link https://time2split.github.io/php-time2help/classes/Time2Split-Help-Optional.html Optional
     */
    public function getOptional($offset, ReadingMode $mode = ReadingMode::Normal): Optional;

    /**
     * Whether an offset is present with a value (the value may be null).
     *
     * @param ?K $offset
     *            An offset to check for.
     * @return bool Returns true on success or false on failure.
     */
    public function isPresent($offset): bool;

    /**
     * Drops all items from the configuration.
     */
    public function clear(): void;

    /**
     * Makes a copy of the configuration.
     *
     * @param Interpolator $interpolator The interpolator to use for the copy.
     *  - (null)
     *  If not set then the copy contains the interpolated value of the configuration tree and will never do interpolation.
     *  - (isset)           
     *  If set then the copy uses this interpolator on the raw base value to create a new interpolated configuration.
     *  Note that the interpolator may be the same as $config, in that case it means that the same interpolation is conserved.
     * 
     * @return static The copy.
     */
    public function copy(?Interpolator $interpolator = null): static;
}
