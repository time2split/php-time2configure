<?php

namespace Time2Split\Config\_private\TreeConfig;

/**
 *
 * @template K
 * @template V
 * 
 * @author Olivier Rodriguez (zuri)
 */
interface TreeStorage
{
    /**
     * @return array<K,V>
     */
    public function getTreeStorage(): array;
}
