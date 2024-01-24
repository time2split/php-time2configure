<?php
namespace Time2Split\Config\_private;

use Time2Split\Config\Configurations;
use Time2Split\Config\Configuration;

trait ConfigUtilities
{

    public function toArray(): array
    {
        return \iterator_to_array($this);
    }

    public function keys(): array
    {
        return \iterator_to_array($this->traversableKeys());
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