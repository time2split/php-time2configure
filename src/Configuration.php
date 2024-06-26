<?php

namespace Time2Split\Config;

use Time2Split\Config\Entry\ReadingMode;

/**
 * A TreeConfiguration with utilities methods.
 * 
 * This is the main class of the library.
 * All implementations provided are Configuration instances.
 * 
 * Instanciations can only be made with methods from {@see Configurations}.
 *
 * @template K
 * @template V
 * @implements TreeConfiguration<K,V>
 * 
 * @author Olivier Rodriguez (zuri)
 * @package time2configure\configuration
 */
abstract class Configuration implements TreeConfiguration
{

    /**
     * Gets an array representation of the entries.
     * 
     * @return array<V>
     */
    public final function toArray(ReadingMode $mode = ReadingMode::Normal): array
    {
        return \iterator_to_array($this->getIterator($mode));
    }

    /**
     * Gets a raw value iterator.
     * 
     * @return \Iterator<K,V|Interpolation<V>>
     */
    public final function getRawValueIterator(): \Iterator
    {
        return $this->getIterator(ReadingMode::RawValue);
    }

    /**
     * Gets a base value iterator.
     * @return \Iterator<K,V>
     */
    public final function getBaseValueIterator(): \Iterator
    {
        return $this->getIterator(ReadingMode::BaseValue);
    }

    /**
     * Adds iterables entries to the configuration.
     * 
     * @param iterable<K,V>  ...$configs Iterables of (K => V) entries.
     * @return static This configuration.
     * @see Configurations::merge()
     */
    public final function merge(iterable ...$configs): static
    {
        Configurations::merge($this, ...$configs);
        return $this;
    }

    /**
     * Merges some trees to the configuration.
     * 
     * @param array<V>  ...$trees Some trees.
     * @return static This configuration.
     * @see Configurations::mergeTree()
     */
    public final function mergeTree(array ...$trees): static
    {
        Configurations::mergeTree($this, ...$trees);
        return $this;
    }

    /**
     * Does the unions with iterables.
     * 
     * @param iterable<K,V>  ...$configs Iterables of (K => V) entries
     * @return static This configuration.
     * @see Configurations::union()
     */
    public final function union(iterable ...$configs): static
    {
        Configurations::union($this, ...$configs);
        return $this;
    }

    /**
     * Unsets some entries.
     * 
     * @param K $offsets Keys to drop.
     * @return static This configuration.
     * @see BaseConfiguration::offsetUnset()
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
     * Removes some nodes (fluent api).
     * @param K $offsets
     * @return static This configuration.
     * @see TreeConfiguration::removeNode()
     */
    public final function removeNodeFluent(...$offsets): static
    {
        foreach ($offsets as $offset)
            $this->removeNode($offset);

        return $this;
    }
}
