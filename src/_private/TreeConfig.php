<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Config\_private\TreeConfig\TreeStorage;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class TreeConfig extends AbstractTreeConfig
{
    use ConfigUtilities;

    // ========================================================================
    public static function rawCopy(Configuration&DelimitedKeys $config)
    {
        $delimiter = $config->getKeyDelimiter();
        $interpolator = $config->getInterpolator();

        if ($config instanceof TreeStorage)
            return new self($delimiter, $interpolator, $config->getTreeStorage());

        throw new \Exception('$config must be a TreeStorage');
    }
}