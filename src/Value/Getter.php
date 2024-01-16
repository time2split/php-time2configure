<?php
namespace Time2Split\Config\Value;

/**
 * Get (or compute) a value from a subject.
 *
 * @author zuri
 */
interface Getter
{

    public function get($subject, ...$data): mixed;
}