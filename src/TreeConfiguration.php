<?php

namespace Time2Split\Config;

/**
 * A configuration with a tree-shaped structure, that is a sequence of (K => V)
 * where V can be a sub-configuration.
 *
 * An entry access is done using a single string key (eg.
 * $config[$key]) representing a path (ie. a branch) in the tree.
 * For now only string keys are allowed, a (near) future extension will be to allow any type of key.
 *
 * Each part of the path is delimited by an internal delimiter character.
 * Each node of the tree can be associated with a value.
 *
 * @template K
 * @template V
 * @extends BaseConfiguration<K,V,mixed>
 * 
 * @author Olivier Rodriguez (zuri)
 * @package time2configure\configuration
 */
interface TreeConfiguration extends BaseConfiguration
{

    /**
     * Whether a node is present in the tree.
     *
     * @param K $path
     *            A path to check for.
     * @return bool Returns true on success or false on failure.
     */
    public function nodeIsPresent($path): bool;

    /**
     * Get a sub-tree copy.
     *
     * @param K $path
     *            A path to the sub-tree.
     * @return static A new instance of the configuration containing the sub-tree.
     */
    public function subTreeCopy($path): static;

    /**
     * Return a view on a sub-tree.
     * 
     * Because of the referencing of a sub-tree, updating one configuration's sub-tree will updates the other.
     *  
     * @param K $path
     *            A path to the sub-tree.
     * @return static A configuration that reference the content of the sub-tree of the initial configuration.
     */
    public function subTreeView($path): static;

    /**
     * Get some branches of the configuration.
     *
     * @param K $path
     *            A first path to a sub-tree.
     * @param K ...$paths
     *            More paths to more sub-trees.
     * @return static A new instance of the configuration containing the selected nodes branches and their sub-tree.
     */
    public function copyBranches($path, ...$paths): static;

    /**
     * Remove a node from the configuration.
     *
     * @param K $path
     *            A path to a node.
     */
    public function removeNode($path): void;
}
