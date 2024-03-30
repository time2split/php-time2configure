<?php

namespace Time2Split\Config;

use Time2Split\Help\Optional;

/**
 * Allows to transform a simple value to a more complex operation.
 *
 * For instance, there is a text Interpolator implementation (Interpolators::recursive()) that parmits to detect ${key} element in a text
 * and substitute it by the $config[$key] value of the Configuration instance.
 *
 * @template V
 * @template I
 * 
 * @author Olivier Rodriguez (zuri)
 */
interface Interpolator
{

    /**
     * Compile a value if possible
     *
     * @param V $value
     *            The value to compile
     * @return Optional<mixed> An optional filled by the compilation
     */
    public function compile($value): Optional;

    /**
     * Execute the compilation (if set) and return the result
     *
     * @param mixed $compilation
     *            The compilation to execute
     * @param Configuration<mixed,V> $config
     *            The Configuration instance to consider
     * @return I The result of the execution of the compilation
     */
    public function execute($compilation, Configuration $config): mixed;
}
