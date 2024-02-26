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

    public function mergeTree(array ...$trees): static;

    public function merge(iterable ...$configs): static;

    public function union(iterable ...$configs): static;
}