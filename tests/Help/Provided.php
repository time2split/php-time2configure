<?php
declare(strict_types = 1);
namespace Time2Split\Config\Tests\Help;

use Time2Split\Help\Arrays;

final class Provided
{

    public function __construct( //
    public readonly string $header, //
    public readonly array $data) //
    {}

    public static function merge(array ...$provided): iterable
    {
        return (function () use ($provided) {
            $prod = Arrays::cartesianProduct(...$provided);
            $prod = Arrays::mergeCartesianProduct($prod);

            foreach ($prod as $line) {
                $header = \implode('/', \array_map(fn ($p) => $p->header, $line));
                $data = \array_merge(...\array_map(fn ($p) => $p->data, $line));

                foreach ($data as $k => $v) {
                    if ($v instanceof Producer)
                        $data[$k] = $v->get();
                }
                yield $header => $data;
            }
        })();
    }
}