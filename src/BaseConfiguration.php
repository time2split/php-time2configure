<?php
namespace Time2Split\Config;

use Time2Split\Help\Optional;
use Time2Split\Config\Entry\ReadingMode;

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
     * @param ReadingMode $mode
     *            The mode with which to retrieves the entry value.
     * @see ReadingMode
     * @see \IteratorAggregate::getIterator()
     */
    public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Iterator;

    /**
     *
     * {@inheritdoc}
     * @param ReadingMode $mode
     *            The mode with which to retrieves the entry value.
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($offset, ReadingMode $mode = ReadingMode::Normal): mixed;

    /**
     * Get the value if set.
     *
     * @param mixed $offset
     *            The offset to retrieve.
     * @param ReadingMode $mode
     *            The mode with which to retrieves the entry value.
     * @return Optional The value to retrieve.
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
     * @param Interpolator $resetInterpolator
     *            If not set (ie. null) the copy will contains the interpolated value of the configuration tree.
     *            If set the copy will use this interpolator on the raw base value to create a new interpolated configuration.
     *            Note that the interpolator may be the same as $config, in that case it means that the base interpolation is conserved.
     * @return self A new Configuration instance.
     */
    public function copy(?Interpolator $interpolator = null): static;
}