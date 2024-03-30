<?php

namespace Time2Split\Config\Entry;

/**
 * Maps an entry value.
 * 
 * A value mapping can be created with {@see Entries::mapValue()}.
 * 
 * @template K
 * @template V
 * @template MV
 * 
 * @extends Map<K,V,K,MV>
 *
 * @author Olivier Rodriguez (zuri)
 * @package time2configure\interpolation
 */
interface MapValue extends Map
{
}
