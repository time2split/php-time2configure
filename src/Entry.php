<?php

namespace Time2Split\Config;

/**
 * A (key => value) representation.
 *
 * @template K
 * @template V
 * 
 * @author zuri
 */
final class Entry
{
    /**
     * @param K $key
     * @param V $value
     */
    public function __construct(public readonly mixed $key, public readonly mixed $value)
    {
    }
}
