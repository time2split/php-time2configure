<?php
namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Configurations;

trait ConfigUtilities
{

    public function toArray(): array
    {
        return \iterator_to_array($this);
    }

    public function merge(Configuration $config): void
    {
        Configurations::merge($this, $config);
    }

    public function union(Configuration $config): void
    {
        Configurations::union($this, $config);
    }
}