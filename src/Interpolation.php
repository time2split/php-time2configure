<?php
namespace Time2Split\Config;

/**
 * An interpolated value is automaticly promoted to an Interpolation value containing the base raw text value and its compiled value.
 *
 * Instances of this class are not intended to be constructed outside a Configuration instance.
 * The class is publicly exposed to permits the `instanceof` test on raw values if needed in some external algorithms.
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class Interpolation
{

    public function __construct(public readonly string $text, public readonly mixed $compilation)
    {}
}