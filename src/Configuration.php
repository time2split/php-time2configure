<?php
namespace Time2Split\Config;

use Time2Split\Config\Entry\ReadingMode;

/**
 * Extends the TreeConfiguration with utilities methods mainly copied from Configurations.
 *
 * @author Olivier Rodriguez (zuri)
 */
abstract class Configuration implements TreeConfiguration
{

    /**
     *
     * @source
     */
    public final function toArray(ReadingMode $mode = ReadingMode::Normal): array
    {
        return \iterator_to_array($this->getIterator($mode));
    }

    /**
     *
     * @source
     */
    public final function rawCopy(): static
    {
        return $this->copy($this->getInterpolator());
    }

    /**
     *
     * @source
     */
    public final function getRawValueIterator(): \Iterator
    {
        return $this->getIterator(ReadingMode::RawValue);
    }

    /**
     *
     * @source
     */
    public final function getBaseValueIterator(): \Iterator
    {
        return $this->getIterator(ReadingMode::BaseValue);
    }

    /**
     *
     * @source
     */
    public final function merge(iterable ...$configs): static
    {
        Configurations::merge($this, ...$configs);
        return $this;
    }

    /**
     *
     * @source
     */
    public final function mergeTree(array ...$trees): static
    {
        Configurations::mergeTree($this, ...$trees);
        return $this;
    }

    /**
     *
     * @source
     */
    public final function union(iterable ...$configs): static
    {
        Configurations::union($this, ...$configs);
        return $this;
    }

    /**
     *
     * @source
     */
    public final function unsetFluent(...$offsets): static
    {
        foreach ($offsets as $offset)
            unset($this[$offset]);

        return $this;
    }

    /**
     *
     * @source
     */
    public final function removeNodeFluent(...$offsets): static
    {
        foreach ($offsets as $offset)
            $this->removeNode($offset);

        return $this;
    }
}