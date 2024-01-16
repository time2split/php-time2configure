<?php
namespace Time2Split\Config\Value;

final class Getters
{

    private function __construct()
    {
        throw new \Error();
    }

    public static function fromCallable(callable $get): Getter
    {
        return new class($get) implements Getter {

            private $get;

            function __construct(callable $get)
            {
                $this->get = $get;
            }

            public function get($subject, ...$data): mixed
            {
                return ($this->get)($subject, ...$data);
            }
        };
    }

    /**
     * Apply each Getter item of an array.
     */
    public static function map(array $getters, $subject, ...$data): array
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