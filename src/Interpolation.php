<?php

namespace Time2Split\Config;

// phpdoc
use Time2Split\Config\Entry\ReadingMode;

/**
 * Inside a configuration,
 * a compiled value is automatically promoted to an interpolation value
 * containing the base value and its compilation.
 * 
 * The interpolation mecanism implies that a stored value in a configuration may be either
 * the base value set by the user, or an interpolation of its base value.
 * An interpolation stores itself the base value and its compilation that, if executed return an interpolated value.
 * 
 * By default, the behaviour of accessing an interpolation is to execute the compilation to return the interpolated value.
 * Nevertheless, it can be usefull to be able to access to the base value, or to the Interpolation value itself.
 * That is for this purpose that the {@see ReadingMode} enum exists.
 * Every base access methods of {@see BaseConfiguration} takes the reading mode as a parameter to choose what is the value to return.
 *
 * @author Olivier Rodriguez (zuri)
 * 
 * @template V
 *
 *
 * @see Interpolator
 * @package time2configure\interpolation
 */
final class Interpolation
{
    /** 
     * @var V $baseValue The base value before it was compiled.
     */
    public readonly mixed $baseValue;

    /** 
     * @var mixed $compilation The compilation made by an interpolator.
     */
    public readonly mixed $compilation;

    /**
     * @internal
     */
    public function __construct(mixed $baseValue,  mixed $compilation)
    {
        $this->baseValue = $baseValue;
        $this->compilation = $compilation;
    }
}
