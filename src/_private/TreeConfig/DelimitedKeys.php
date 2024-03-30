<?php

namespace Time2Split\Config\_private\TreeConfig;

/**
 * @author Olivier Rodriguez (zuri)
 */
interface DelimitedKeys
{

    public function getKeyDelimiter(): string;

    /**
     * @param array<mixed> $path
     */
    public function pathToOffset(array $path): mixed;
}
