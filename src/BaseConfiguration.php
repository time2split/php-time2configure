<?php
namespace Time2Split\Config;

use Time2Split\Help\Optional;

/**
 * A set of accessible and modifiable key => value entries.
 * This interface does not describe the format and the semantic of the key part,
 * it may be as simple of php array keys, or be more complex like a hierarchical structure "a.b.c".
 *
 * @author Olivier Rodriguez (zuri)
 *
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
     *
     * {@inheritdoc}
     * @see \IteratorAggregate::getIterator()
     */
    public function getIterator(): \Iterator;

    /**
     * Retrieve an iterator to iterate through the raw stored value including Interpolation values.
     *
     * @return \Iterator An iterator instance.
     */
    public function getRawValueIterator(): \Iterator;

    /**
     * Get the value if set.
     *
     * @param mixed $offset
     *            The offset to retrieve.
     * @param bool $interpolate
     *            Set to true if the value must be interpolated, set to false if the raw Interpolation values must be retrieved.
     * @return Optional The value to retrieve.
     */
    public function getOptional($offset, bool $interpolate = true): Optional;

    /**
     * Whether an offset is present (the value may be null).
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
}