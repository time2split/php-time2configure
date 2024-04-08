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
     *            The configuration of the (key => value) entry.
     * @param K $key
     *            The key of the entry.
     * @param V $value
     *            The value of the entry.
     */
    public function consume(Configuration $config, $key, $value): void;
}
