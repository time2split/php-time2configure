<?php

declare(strict_types=1);

namespace Time2Split\Config\Tests;

use PHPUnit\Framework\TestCase;

final class AssertIsActiveTest extends TestCase
{
    public function testAssert(): void
    {
        $this->expectException(\AssertionError::class);
        assert(false);
    }
}
