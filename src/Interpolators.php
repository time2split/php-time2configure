<?php
namespace Time2Split\Config;

use Time2Split\Config\_private\InterpolationReader;
use Time2Split\Help\Optional;

class Interpolators
{
    use \Time2Split\Help\Classes\NotInstanciable;

    private static $null;

    public static function null(): Interpolator
    {
        return self::$null ??= new class() implements Interpolator {

            public function compile($value): Optional
            {
                return Optional::empty();
            }

            public function execute($value, IConfig $config): Optional
            {
                return Optional::empty();
            }
        };
    }

    private static $recursive;

    public static function recursive(): Interpolator
    {
        return self::$recursive ??= new class() implements Interpolator {

            public function compile($value): Optional
            {
                if (! is_string($value))
                    return Optional::empty();

                $reader = InterpolationReader::create();
                $res = $reader->for($value);

                if (! $res->isPresent())
                    return Optional::empty();

                $res = new Interpolation($value, $res->get());
                return Optional::of($res);
            }

            public function execute($value, IConfig $config): Optional
            {
                if ($value instanceof Interpolation && $value->compilation instanceof Value\Getter) {
                    $v = $value->compilation->get($config);

                    if (\is_array($v))
                        $v = \implode('', $v);

                    return Optional::of($v);
                }
                return Optional::empty();
            }
        };
    }
}