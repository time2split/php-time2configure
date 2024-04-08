<?php

namespace Time2Split\Config\Entry;

use Time2Split\Config\Configuration;
use Time2Split\Config\Entry;
// phpdoc
use Time2Split\Config\Entries;

/**
 * Maps an entry.
 * 
 * Such a mapping can be created with {@see Entries::mapEntry()}.
 * 
 * @template K
 * @template V
 * @template MK
 * @template MV
 * 
 * @author Olivier Rodriguez (zuri)
 * @package time2configure\interpolation
 */
interface Map
{

    /**
     * Maps an entry to a new one.
     *
     * @param Configuration<K,V> $config
     *            The configuration where the entry belongs to.
     * @param K $key
     *            The key to map.
     * @param V $value
     *            The value to map.
     * @return Entry<MK,MV> The produced entry.
     */
    public function map(Configuration $config, $key, $value): Entry;

    /**
     * Views the Map as a consumer (eg: ignore its result).
     * 
     * @return Consumer<K,V>
     */
    public function asConsumer(): Consumer;
}
