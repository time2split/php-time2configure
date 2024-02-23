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

    public function merge(iterable $config): static
    {
        Configurations::merge($this, $config);
        return $this;
    }

    public function mergeTree(array $tree): static
    {
        Configurations::mergeTree($this, $tree);
        return $this;
    }

    public function union(iterable $config): static
    {
        Configurations::union($this, $config);
        return $this;
    }
}