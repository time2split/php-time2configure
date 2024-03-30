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
     * Get an array representation of the entries.
     * 
     * @return array<V>
     */
    public final function toArray(ReadingMode $mode = ReadingMode::Normal): array
    {
        return \iterator_to_array($this->getIterator($mode));
    }

    /**
     * Get a raw value iterator.
     * 
     * @return \Iterator<K,V|Interpolation<V>>
     */
    public final function getRawValueIterator(): \Iterator
    {
        return $this->getIterator(ReadingMode::RawValue);
    }

    /**
     * Get a base value iterator.
     * @return \Iterator<K,V>
     * 
     * @source
     */
    public final function getBaseValueIterator(): \Iterator
    {
        return $this->getIterator(ReadingMode::BaseValue);
    }

    /**
     * Add iterable entries to the configuration.
     * 
     * @param iterable<K,V>  ...$configs Iterables of (K => V) entries.
     * @see Configurations::merge()
     */
    public final function merge(iterable ...$configs): static
    {
        Configurations::merge($this, ...$configs);
        return $this;
    }

    /**
     * Merge some trees to the configuration.
     * 
     * @param array<V>  ...$trees Some trees.
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
     * @see Configurations::union()
     */
    public final function union(iterable ...$configs): static
    {
        Configurations::union($this, ...$configs);
        return $this;
    }

    /**
     * Unset some entries.
     * 
     * @param K $offsets Keys to drop.
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
     * Remove some nodes (fluent api).
     * @param K $offsets
     * @return static This configuration.
     * @see BaseConfiguration::removeNode()
     */
    public final function removeNodeFluent(...$offsets): static
    {
        foreach ($offsets as $offset)
            $this->removeNode($offset);

        return $this;
    }
}
