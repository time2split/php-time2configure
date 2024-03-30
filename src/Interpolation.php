<?php

namespace Time2Split\Config;

/**
 * Inside a {@link Configuration},
 * a compiled value must be automatically promoted to an {@link Interpolation} value
 * containing the *base value* and its *compilation*.
 *
 * @author Olivier Rodriguez (zuri)
 * 
 * @template V
 *
 * @property-read V $baseValue The base value initially assigned in the configuration.
 * @property-read mixed $compilation The compilation made by the configuration {@link Interpolator}.
 *
 * @see Interpolator
 */
final class Interpolation
{

    /**
     * @param V $baseValue
     * 
     * @internal
     */
    public function __construct(public readonly mixed $baseValue, public readonly mixed $compilation)
    {
    }
}
