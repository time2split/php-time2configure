<?php

namespace Time2Split\Config\Entry;

/**
 * Maps an entry key.
 * 
 * A key mapping can be created with {@see Entries::mapKey()}.
 * 
 * @template K
 * @template V
 * @template MK
 * 
 * @extends Map<K,V,MK,V>
 *
 * @author Olivier Rodriguez (zuri)
 * @package time2configure\interpolation
 */
interface MapKey extends Map
{
}
