<?php
namespace Time2Split\Config\Entry;

use Time2Split\Config\Configuration;
use Time2Split\Config\Entry;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
interface Map
{

    /**
     * Map a key and/or a value to a new one.
     *
     * @param Configuration $config
     *            The configuration from/to map the key => value Entry.
     * @param mixed $key
     *            The key to map.
     * @param mixed $value
     *            The value to map.
     * @return Entry The produced newkey => newvalue entry.
     */
    public function map(Configuration $config, $key, $value): Entry;

    /**
     * View the Map as a {@source Consumer} (eg ignore its result).
     */
    public function asConsumer(): Consumer;
}