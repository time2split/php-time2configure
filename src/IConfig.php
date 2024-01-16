<?php
namespace Time2Split\Config;

use Time2Split\Help\Optional;

/**
 * A set of accessible and modifiable key => value pairs.
 * This interface does not describe what is the format and the semantic of the key part,
 * it may be the same as the keys in an array, or be more complex like a hierarchical structure "a.b.c".
 *
 * @author zuri
 */
interface IConfig extends \ArrayAccess, \Traversable
{

    public function getInterpolator(): Interpolator;

    /**
     * An access key of $this may be composed of multiple parts defining a path in the configuration tree.
     * The delimiter is a character that permits to split a key in parts.
     */
    public function getKeyDelimiter(): string;

    // ========================================================================
    public function traversableKeys(): \Traversable;

    public function getOptional($offset): Optional;

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
     * of the new IConfig instance.
     */
    public function subConfig($offset): static;

    /**
     * Select some sub-trees of $this but preserve its parent $offset.
     */
    public function select($offset, ...$moreOffsets): static;

    // ========================================================================
    // Utilities
    public function toArray(): array;

    public function keys(): array;

    public function flatMerge(array|\Traversable $array): void;

    public function merge(array|\Traversable $data): void;
}