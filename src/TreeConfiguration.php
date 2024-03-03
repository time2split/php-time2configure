<?php
namespace Time2Split\Config;

/**
 * A TreeConfiguration has a tree-shaped structure.
 * An entry access is done with a single key (eg.
 * $config[$key]) representing a path in the tree.
 *
 * Each part of the path is delimited by an internal delimiter character.
 * Each node of the tree can be associate with a value.
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
interface TreeConfiguration extends BaseConfiguration
{

    /**
     * Whether a node is present in the tree.
     *
     * @param mixed $offset
     *            An offset to check for.
     * @return bool Returns true on success or false on failure.
     */
    public function nodeIsPresent($offset): bool;

    /**
     * Get a sub-tree copy.
     *
     * @return static A new instance of the configuration containing the sub-tree.
     */
    public function subTreeCopy($offset): static;

    /**
     * Return a view on a sub-tree.
     *
     * @return static A configuration that reference the content of the sub-tree of the initial configuration.
     */
    public function subTreeView($offset): static;

    /**
     * Get some branches of the configuration.
     *
     * @return static A new instance of the configuration containing the selected nodes branches and their sub-tree.
     */
    public function copyBranches($offset, ...$offsets): static;

    /**
     * Remove a node from the configuration.
     */
    public function removeNode($offset): void;
}