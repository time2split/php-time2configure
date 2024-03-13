<?php
declare(strict_types = 1);
namespace Time2Split\Config;

use Time2Split\Config\Entry\Consumer;
use Time2Split\Config\Entry\MapKey;
use Time2Split\Config\Entry\MapValue;
use Time2Split\Config\_private\Entry\AMapKey;
use Time2Split\Config\_private\Entry\AMapKeyValue;
use Time2Split\Config\_private\Entry\AMapValue;
use Time2Split\Config\_private\Entry\AbstractConsumer;
use Time2Split\Help\Classes\NotInstanciable;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class Entries
{
    use NotInstanciable;

    // ========================================================================
    // MAP FROM CLOSURES
    // ========================================================================
    public static function mapKey(\Closure $map): MapKey
    {
        return new class($map) extends AMapKey {

            public function map(Configuration $config, $key, $value): Entry
            {
                return new Entry(($this->map)($key, $config), $value);
            }
        };
    }

    public static function mapKeyFromValue(\Closure $map): MapKey
    {
        return new class($map) extends AMapKey {

            public function map(Configuration $config, $key, $value): Entry
            {
                return new Entry(($this->map)($value, $key, $config), $value);
            }
        };
    }

    public static function mapValue(\Closure $map): MapValue
    {
        return new class($map) extends AMapValue {

            public function map(Configuration $config, $key, $value): Entry
            {
                return new Entry($key, ($this->map)($value, $key, $config));
            }
        };
    }

    public static function mapValueFromKey(\Closure $map): MapValue
    {
        return new class($map) extends AMapValue {

            public function map(Configuration $config, $key, $value): Entry
            {
                return new Entry($key, ($this->map)($key, $config));
            }
        };
    }

    public static function mapEntry(\Closure $map): MapKey&MapValue
    {
        return new class($map) extends AMapKeyValue {

            public function map(Configuration $config, $key, $value): Entry
            {
                return ($this->map)($key, $value, $config);
            }
        };
    }

    // ========================================================================
    // CONSUMMER
    // ========================================================================
    public static function consumeEntry(\Closure $consumer): Consumer
    {
        return new class($consumer) extends AbstractConsumer {

            public function consume(Configuration $config, $key, $value): void
            {
                ($this->consumer)($key, $value, $config);
            }
        };
    }
}