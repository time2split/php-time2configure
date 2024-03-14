<?php
namespace Time2Split\Config\Entry;

use Time2Split\Config\Interpolation;

/**
 *
 * Possible modes when accessing an entry value of a configuration.
 *
 * @author Olivier Rodriguez (zuri)
 * @see ReadingMode::Interpolate
 * @see ReadingMode::RawValue
 * @see ReadingMode::BaseValue
 * @see ReadingMode::Normal
 * @see Interpolation
 */
enum ReadingMode
{

    /**
     * Retrieves the interpolated value of an entry, or the value iteself if not an {@link Interpolation}.
     */
    case Interpolate;

    /**
     * Retrieves the stored raw value of an entry.
     */
    case RawValue;

    /**
     * Retrieves either the {@link Interpolation} base value, or the value iteself if not an {@link Interpolation}.
     */
    case BaseValue;

    /**
     * The normal configuration behaviour is to interpolate the entry values.
     *
     * @see ReadingMode::Interpolate
     */
    public const Normal = self::Interpolate;
}
// Avoid IDE Warnings
return;
Interpolation::class;