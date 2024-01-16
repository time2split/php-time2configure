<?php
namespace Time2Split\Config\_private;

use Time2Split\Config\Configs;

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
        Configs::flatMerge($this, $array);
    }

    public function merge(array|\Traversable $data): void
    {
        Configs::merge($this, $data);
    }
}