<?php
namespace Time2Split\Config;

/**
 * A TreeConfiguration has a tree-shaped structure.
 *
 * An entry access is done using a single key (eg.
 * $config[$key]) representing a path (ie. a branch) in the tree.
 *
 * Each part of the path is delimited by an internal delimiter character.
 * Each node of the tree can be associated with a value.
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
interface TreeConfiguration extends BaseConfiguration
{

    /**
     * Whether a node is present in the tree.
     *
     * @param mixed $path
     *            A path to check for.
     * @return bool Returns true on success or false on failure.
     */
    public function nodeIsPresent($path): bool;

    /**
     * Get a sub-tree copy.
     *
     * @param mixed $path
     *            A path to the sub-tree.
     * @return static A new instance of the configuration containing the sub-tree.
     */
    public function subTreeCopy($path): static;

    /**
     * Return a view on a sub-tree.
     *
     * @param mixed $path
     *            A path to the sub-tree.
     * @return static A configuration that reference the content of the sub-tree of the initial configuration.
     */
    public function subTreeView($path): static;

    /**
     * Get some branches of the configuration.
     *
     * @param mixed $path
     *            A first path to a sub-tree.
     * @param mixed ...$paths
     *            More paths to more sub-trees.
     * @return static A new instance of the configuration containing the selected nodes branches and their sub-tree.
     */
    public function copyBranches($path, ...$paths): static;

    /**
     * Remove a node from the configuration.
     *
     * @param mixed $path
     *            A path to a node.
     */
    public function removeNode($path): void;
}