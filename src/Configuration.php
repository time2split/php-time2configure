<?php

namespace Time2Split\Config;

use Time2Split\Config\Entry\ReadingMode;

/**
 * Extends the TreeConfiguration with utilities methods mainly copied from Configurations.
 *
 * @template K
 * @template V
 * @implements TreeConfiguration<K,V>
 * 
 * @author Olivier Rodriguez (zuri)
 */
abstract class Configuration implements TreeConfiguration
{

    /**
     * Get an array representation of the entries.
     * 
     * @return array<V>
     * 
     * @source
     */
    public final function toArray(ReadingMode $mode = ReadingMode::Normal): array
    {
        return \iterator_to_array($this->getIterator($mode));
    }

    /**
     * Make a raw copy of the entries, that is conserve the value with interpolations.
     * 
     * @source
     */
    public final function rawCopy(): static
    {
        return $this->copy($this->getInterpolator());
    }

    /**
     * Get a raw value iterator.
     * @return \Iterator<K,V|Interpolation<V>>
     * 
     * @source
     */
    public final function getRawValueIterator(): \Iterator
    {
        return $this->getIterator(ReadingMode::RawValue);
    }

    /**
     * @return \Iterator<K,V>
     * 
     * @source
     */
    public final function getBaseValueIterator(): \Iterator
    {
        return $this->getIterator(ReadingMode::BaseValue);
    }

    /**
     * @param iterable<K,V>  ...$configs
     * @source
     */
    public final function merge(iterable ...$configs): static
    {
        Configurations::merge($this, ...$configs);
        return $this;
    }

    /**
     * @param array<K,V>  ...$trees
     * @source
     */
    public final function mergeTree(array ...$trees): static
    {
        Configurations::mergeTree($this, ...$trees);
        return $this;
    }

    /**
     *
     * @param iterable<K,V>  ...$configs
     * @source
     */
    public final function union(iterable ...$configs): static
    {
        Configurations::union($this, ...$configs);
        return $this;
    }

    /**
     * @param K $offsets
     * @source
     */
    public final function unsetFluent(...$offsets): static
    {
        foreach ($offsets as $offset)
            unset($this[$offset]);

        // Bug with phpstan: 
        // Method Time2Split\Config\Configuration::unsetFluent() should return static(Time2Split\Config\Configuration<K, V>) but returns Time2Split\Config\Configuration<K, V>.
        return $this;
    }

    /**
     * @param K $offsets
     * @source
     */
    public final function removeNodeFluent(...$offsets): static
    {
        foreach ($offsets as $offset)
            $this->removeNode($offset);

        return $this;
    }
}
