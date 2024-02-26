<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Configurations;
use Time2Split\Config\Interpolator;

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

    public function copy(?Interpolator $interpolator = null): Configuration
    {
        return Configurations::copyOf($this, $interpolator);
    }
}