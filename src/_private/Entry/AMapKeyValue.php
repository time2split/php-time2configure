<?php
namespace Time2Split\Config\_private\Entry;

use Time2Split\Config\Entry\MapKey;
use Time2Split\Config\Entry\MapValue;

/**
 * Just to have a better invalid type message.
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class AMapKeyValue extends AbstractMap implements MapKey, MapValue
{
}