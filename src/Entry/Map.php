<?php

namespace Time2Split\Config\Entry;

use Time2Split\Config\Configuration;
use Time2Split\Config\Entry;
// phpdoc
use Time2Split\Config\Entries;

/**
 * Maps an entry.
 * 
 * An entry mapping can be create with {@see Entries::mapEntry()}.
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
     *            The configuration from/to map the key => value Entry.
     * @param K $key
     *            The key to map.
     * @param V $value
     *            The value to map.
     * @return Entry<MK,MV> The produced (newkey => newvalue) entry.
     */
    public function map(Configuration $config, $key, $value): Entry;

    /**
     * Views the Map as a consumer (eg ignore its result).
     * 
     * @returm Consumer<K,V>
     */
    public function asConsumer(): Consumer;
}
