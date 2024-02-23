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

    public function getInterpolator(): Interpolator;

    // ========================================================================

    /**
     *
     * {@inheritdoc}
     * @see \IteratorAggregate::getIterator()
     */
    public function getIterator(): \Iterator;

    public function getOptional($offset, bool $interpolate = true): Optional;

    public function isPresent($offset): bool;

    /**
     * Drop all items from $this.
     */
    public function clear(): void;
}