<?php
namespace Time2Split\Config;

/**
 * A key => value array/Configuration entry.
 *
 * @author zuri
 *
 */
final class Entry
{

    public function __construct(public readonly mixed $key, public readonly mixed $value)
    {}
}