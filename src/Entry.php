<?php
namespace Time2Split\Config;

/**
 * Represents a key => value array entry.
 *
 * @author zuri
 *
 */
final class Entry
{

    public function __construct(public readonly mixed $key, public readonly mixed $value)
    {}
}