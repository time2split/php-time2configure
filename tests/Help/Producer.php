<?php
declare(strict_types = 1);
namespace Time2Split\Config\Tests\Help;

final class Producer
{

    public function __construct(private readonly \Closure $get) //
    {}

    public function get(): mixed
    {
        return ($this->get)();
    }
}