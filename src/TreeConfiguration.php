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
     * Get a sub-tree copy.
     *
     * @return static A new instance of the configuration containing the sub-tree.
     */
    public function subTreeCopy($offset): static;

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