<?php

namespace Time2Split\Config;

use Time2Split\Help\Optional;

/**
 * Allows to transform a simple value to a more complex operation.
 *
 * An interpolator is associated to a configuration instance, and transforms automatically every value he can recognize
 * to a compiled process wrapped in a {@see Interpolation}.
 * This compilation is executed each time an interpolated value is accessed.
 * 
 * For instance, there is a text interpolator implementation ({@see Interpolators::recursive()}) that can detect '${key}' token element
 * in a text string and make a compilation that substitute such a token by the $config[$key] value of the configuration instance when
 * the string is accessed.
 *
 * @template V
 * @template I
 * 
 * @author Olivier Rodriguez (zuri)
 * @package time2configure\interpolation
 */
interface Interpolator
{

    /**
     * Compiles a value if possible
     *
     * @param V $value
     *            The value to compile
     * @return Optional<mixed> An optional filled by the compilation
     */
    public function compile($value): Optional;

    /**
     * Executes the compilation (if set) and return the result
     *
     * @param mixed $compilation
     *            The compilation to execute
     * @param Configuration<mixed,V> $config
     *            The Configuration instance to consider
     * @return I The result of the execution of the compilation
     */
    public function execute($compilation, Configuration $config): mixed;
}
