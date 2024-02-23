<?php
namespace Time2Split\Config;

use Time2Split\Help\Optional;

/**
 * A set of accessible and modifiable key => value entries.
 * This interface does not describe the format and the semantic of the key part,
 * it may be as simple of php array keys, or be more complex like a hierarchical structure "a.b.c".
 *
 * @author zuri
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

    // ========================================================================

    /**
     * Return a new instance of the configuration using another $interpolator.
     */
    public function resetInterpolator(Interpolator $interpolator): static;

    /**
     * Select a sub-tree of $this and set its root to be the one
     * of the new Configuration instance.
     */
    public function subConfig($offset): static;

    /**
     * Select some sub-trees of $this but preserve their parent $offset.
     */
    public function select($offset, ...$moreOffsets): static;
}