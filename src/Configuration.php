<?php
namespace Time2Split\Config;

/**
 * Extends the TreeConfiguration with utilities methods mainly copied from Configurations.
 *
 * @author Olivier Rodriguez (zuri)
 */
abstract class Configuration implements TreeConfiguration
{

    public final function toArray(bool $interpolate = true): array
    {
        return \iterator_to_array($this->getIterator($interpolate));
    }

    public final function rawCopy(): static
    {
        return $this->copy($this->getInterpolator());
    }

    public final function getRawValueIterator(): \Iterator
    {
        return $this->getIterator(false);
    }

    public final function merge(iterable ...$configs): static
    {
        Configurations::merge($this, ...$configs);
        return $this;
    }

    public final function mergeTree(array ...$trees): static
    {
        Configurations::mergeTree($this, ...$trees);
        return $this;
    }

    public final function union(iterable ...$configs): static
    {
        Configurations::union($this, ...$configs);
        return $this;
    }

    public final function unsetFluent(...$offsets): static
    {
        foreach ($offsets as $offset)
            unset($this[$offset]);

        return $this;
    }

    public final function removeNodeFluent(...$offsets): static
    {
        foreach ($offsets as $offset)
            $this->removeNode($offset);

        return $this;
    }
}