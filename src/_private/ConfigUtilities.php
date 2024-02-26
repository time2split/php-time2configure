<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private;

use Time2Split\Config\Configurations;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
trait ConfigUtilities
{

    public function toArray(): array
    {
        return \iterator_to_array($this);
    }

    public function merge(iterable ...$configs): static
    {
        Configurations::merge($this, ...$configs);
        return $this;
    }

    public function mergeTree(array ...$trees): static
    {
        Configurations::mergeTree($this, ...$trees);
        return $this;
    }

    public function union(iterable ...$configs): static
    {
        Configurations::union($this, ...$configs);
        return $this;
    }
}