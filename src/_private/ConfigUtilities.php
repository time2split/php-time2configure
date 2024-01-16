<?php
namespace Time2Split\Config\_private;

use Time2Split\Config\Configurations;

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

    public function flatMerge(array|\Traversable $array): void
    {
        Configurations::flatMerge($this, $array);
    }

    public function merge(array|\Traversable $data): void
    {
        Configurations::merge($this, $data);
    }
}