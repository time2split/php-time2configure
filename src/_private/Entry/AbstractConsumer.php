<?php
namespace Time2Split\Config\_private\Entry;

use Time2Split\Config\Entry\Consumer;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class AbstractConsumer implements Consumer
{

    public function __construct(protected readonly \Closure $consumer)
    {}
}