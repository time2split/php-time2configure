<?php

namespace Time2Split\Config\Entry;

use Time2Split\Config\Configuration;
// phpdoc
use Time2Split\Config\Entries;

/**
 * Consumes an entry.
 * 
 * A Consumer can be create with {@see Entries::consumeEntry()}.
 * 
 * @template K
 * @template V
 * 
 * @author Olivier Rodriguez (zuri)
 * @package time2configure\interpolation
 */
interface Consumer
{

    /**
     * Consumes an entry to do an action.
     *
     * @param Configuration<K,V> $config
     *            The configuration from/to map the key => value Entry.
     * @param K $key
     *            The key to map.
     * @param V $value
     *            The value to map.
     */
    public function consume(Configuration $config, $key, $value): void;
}
