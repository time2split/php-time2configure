<?php

namespace Time2Split\Config\_private\Entry;

use Time2Split\Config\Configuration;
use Time2Split\Config\Entries;
use Time2Split\Config\Entry\Consumer;
use Time2Split\Config\Entry\Map;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class AbstractMap implements Map
{

    public function __construct(protected readonly \Closure $map)
    {
    }

    public function asConsumer(): Consumer
    {
        return Entries::mapAsConsumer($this);
    }
}
