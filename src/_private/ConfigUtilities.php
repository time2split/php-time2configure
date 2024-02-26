<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private;

use Time2Split\Config\Configurations;
use Time2Split\Config\Interpolator;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
trait ConfigUtilities
{

    // ========================================================================
    // FLUENT
    // ========================================================================
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

    // ========================================================================
    // NEW INSTANCE
    // ========================================================================
    public function resetInterpolator(Interpolator $interpolator): static
    {
        return Configurations::resetInterpolator($this, $interpolator);
    }
}