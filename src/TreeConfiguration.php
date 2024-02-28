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
     * Select a sub-tree configuration.
     */
    public function subConfig($offset): static;

    /**
     * Select some sub-trees and preserve also their parent branch to the root node.
     */
    public function select($offset, ...$offsets): static;

    public function removeNode($offsets): void;
}