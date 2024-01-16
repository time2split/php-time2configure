<?php
namespace Time2Split\Config\_private;

final class Interpreters
{
    use \Time2Split\Help\Classes\NotInstanciable;

    public static function getArrayValueFunction(mixed $key): callable
    {
        return function (mixed $array) use ($key): mixed {
            return $array[$key];
        };
    }
}