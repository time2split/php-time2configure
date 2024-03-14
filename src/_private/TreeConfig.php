<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolator;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Config\_private\TreeConfig\TreeStorage;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
final class TreeConfig extends AbstractTreeConfig
{

    public function copy(?Interpolator $interpolator = null): static
    {
        $ret = new self($this->delimiter, $interpolator ?? $this->interpolator);
        $this->copyToAbstract($ret, $interpolator);
        return $ret;
    }

    public static function rawCopyOf(Configuration&DelimitedKeys $config)
    {
        $delimiter = $config->getKeyDelimiter();
        $interpolator = $config->getInterpolator();

        if ($config instanceof TreeStorage)
            return new self($delimiter, $interpolator, $config->getTreeStorage());

        $ret = new self($delimiter, $interpolator);

        foreach ($config->getRawValueIterator() as $k => $v)
            $ret[$k] = $v;

        return $ret;
    }
}