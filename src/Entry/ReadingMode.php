<?php

namespace Time2Split\Config\Entry;

use Time2Split\Config\Interpolation;

/**
 *
 * Possible modes when accessing an entry value of a configuration.
 *
 * @author Olivier Rodriguez (zuri)
 * @see Interpolation
 */
enum ReadingMode: int
{

    /**
     * Retrieves the interpolated value of an entry, or the value iteself if not an {@link Interpolation}.
     */
    case Interpolate = 0;

    /**
     * Retrieves the stored raw value of an entry.
     */
    case RawValue = 1;

    /**
     * Retrieves either the {@link Interpolation} base value, or the value iteself if not an {@link Interpolation}.
     */
    case BaseValue = 2;

    /**
     * The normal configuration behaviour is to interpolate the entry values.
     *
     * @see ReadingMode::Interpolate
     */
    public const Normal = self::Interpolate;
}
