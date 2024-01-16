<?php
namespace Time2Split\Config;

use Time2Split\Config\_private\InterpolationReader;
use Time2Split\Config\_private\Value\Getter;
use Time2Split\Help\Optional;
use Time2Split\Help\Classes\NotInstanciable;

/**
 * Method Factories that proposes some default Interpolator implementations
 *
 * @author zuri
 *
 */
class Interpolators
{
    use NotInstanciable;

    private static $null;

    /**
     * Corresponds to the null Pattern implementation, that is an Interpolator doing nothing.
     *
     * @return Interpolator
     */
    public static function null(): Interpolator
    {
        return self::$null ??= new class() implements Interpolator {

            public function compile($value): Optional
            {
                return Optional::empty();
            }

            public function execute($value, Configuration $config): Optional
            {
                return Optional::empty();
            }
        };
    }

    private static $recursive;

    /**
     * An Interpolator that can substitute ${key} elements in a text by their corresponding value in the Configuration instance.
     *
     * If a substitution contains more interpolations, then they are recursively processed.
     * The Interpolator can't handles cycles: it will process forever (until php detect a too deep call stack).
     *
     * @return Interpolator The interpolator
     */
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

            public function execute($value, Configuration $config): Optional
            {
                if ($value instanceof Interpolation && $value->compilation instanceof Getter) {
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