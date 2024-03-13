<?php
namespace Time2Split\Config\Entry;

use Time2Split\Config\Configuration;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
interface Consumer
{

    /**
     * Consume a key and/or a value to do an action.
     *
     * @param Configuration $config
     *            The configuration from/to map the key => value Entry.
     * @param mixed $key
     *            The key to map.
     * @param mixed $value
     *            The value to map.
     */
    public function consume(Configuration $config, $key, $value): void;
}