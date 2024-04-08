<?php

namespace Time2Split\Config\Entry;

/**
 *
 * Possible modes when accessing an entry value of a configuration.
 *
 * @author Olivier Rodriguez (zuri)
 * @see Interpolation
 * @package time2configure\configuration
 */
enum ReadingMode: int
{
    /**
     * Retrieves the interpolated value of an entry, or the raw value itself if not an Interpolation.
     * 
     * This is also the value of ReadingMode::Normal
     * (phpdoc don't seems to want to document this constant for now).
     */
    case Interpolate = 0;

    /**
     * Retrieves the stored raw value of an entry.
     */
    case RawValue = 1;

    /**
     * Retrieves either the interpolation base value, or the raw value itself if not an Interpolation.
     */
    case BaseValue = 2;

    /**
     * The normal configuration behaviour is to interpolate the entry values.
     */
    public const Normal = self::Interpolate;
}
