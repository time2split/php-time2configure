<?php

namespace Time2Split\Config\Entry;

use Time2Split\Config\Configuration;

/**
 * @template K
 * @template V
 * 
 * @author Olivier Rodriguez (zuri)
 */
interface Consumer
{

    /**
     * Consume a key and/or a value to do an action.
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
