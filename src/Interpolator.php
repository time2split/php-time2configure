<?php
namespace Time2Split\Config;

use Time2Split\Help\Optional;

/**
 * An Interpolator permits to transform a simple value to a more complex operation.
 *
 * For instance there is a text Interpolator implementation (Interpolators::recursive()) that parmits to detect ${key} element in a text
 * and substitute it by the $config[$key] value of the Configuration instance.
 *
 * @author zuri
 *
 */
interface Interpolator
{

    /**
     * Compile a value if an interpolation is possible.
     *
     * @param mixed $value
     *            The value to compile
     * @return Optional An optional filled if there is a compilation
     */
    public function compile($value): Optional;

    /**
     * Execute the compilation (if set) and return the result
     *
     * @param mixed $value
     *            The value to execute
     * @param Configuration $config
     *            The Configuration instance to consider
     * @return Optional An Optional filled by the execution of the compilation (if $value is really a compilation)
     */
    public function execute($value, Configuration $config): Optional;
}