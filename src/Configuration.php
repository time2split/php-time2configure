<?php
namespace Time2Split\Config;

/**
 * Extends the TreeConfiguration with utilities methods mainly copied from Configurations.
 *
 * @author Olivier Rodriguez (zuri)
 */
interface Configuration extends TreeConfiguration
{

    public function toArray(): array;

    public function mergeTree(array ...$trees): static;

    public function merge(iterable ...$configs): static;

    public function union(iterable ...$configs): static;

    public function copy(?Interpolator $interpolator = null): self;

    public function unsetFluent(...$offsets): static;

    public function removeNodeFluent(...$offsets): static;
}