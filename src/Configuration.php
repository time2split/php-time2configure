<?php
namespace Time2Split\Config;

/**
 * Extends the BaseConfiguration with utilities methods.
 *
 * @author Olivier Rodriguez (zuri)
 */
interface Configuration extends TreeConfiguration
{

    public function toArray(): array;

    public function mergeTree(array $tree): static;

    public function merge(Configuration $config): static;

    public function union(Configuration $config): static;
}