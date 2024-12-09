<?php

declare(strict_types=1);

namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Exception\UnmodifiableException;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
final class UnmodifiableDecorator extends Decorator
{

    public function offsetSet($offset, $value): void
    {
        throw new UnmodifiableException();
    }

    public function offsetUnset($offset): void
    {
        throw new UnmodifiableException();
    }

    public function offsetUnsetNode($offset): void
    {
        throw new UnmodifiableException();
    }
}
