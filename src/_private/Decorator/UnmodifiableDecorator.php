<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Exception\UnmodifiableException;

/**
 *
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

    public function removeNode($offset): void
    {
        throw new UnmodifiableException();
    }

    public function mergeTree(array ...$trees): static
    {
        throw new UnmodifiableException();
    }

    public function merge(iterable ...$configs): static
    {
        throw new UnmodifiableException();
    }

    public function union(iterable ...$configs): static
    {
        throw new UnmodifiableException();
    }

    public function unsetFluent(...$offsets): static
    {
        throw new UnmodifiableException();
    }

    public function removeNodeFluent(...$offsets): static
    {
        throw new UnmodifiableException();
    }
}