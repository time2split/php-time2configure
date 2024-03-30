<?php

namespace Time2Split\Config\_private\Value;

final class Getters
{

    private function __construct()
    {
        throw new \Error();
    }

    public static function fromClosure(\Closure $get): Getter
    {
        return new class($get) implements Getter
        {

            private \Closure $get;

            function __construct(\Closure $get)
            {
                $this->get = $get;
            }

            public function get(mixed $subject, mixed ...$data): mixed
            {
                return ($this->get)($subject, ...$data);
            }
        };
    }

    /**
     * Apply each Getter item of an array.
     * 
     * @param mixed[] $getters
     * @return mixed[]
     */
    public static function map(array $getters, mixed $subject, mixed ...$data): array
    {
        $ret = [];

        foreach ($getters as $getter) {

            if ($getter instanceof Getter)
                $ret[] = $getter->get($subject, ...$data);
            else
                $ret[] = $getter;
        }
        return $ret;
    }
}
