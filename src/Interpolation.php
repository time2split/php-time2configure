<?php
namespace Time2Split\Config;

final class Interpolation
{

    public function __construct(public readonly string $text, public readonly mixed $compilation)
    {}
}