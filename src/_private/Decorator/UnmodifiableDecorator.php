<?php

declare(strict_types=1);

namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Interpolator;
use Time2Split\Help\Container\Class\IsUnmodifiable;
use Time2Split\Help\Container\Trait\UnmodifiableContainerAA;
use Time2Split\Help\Exception\UnmodifiableException;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
final class UnmodifiableDecorator extends Decorator implements IsUnmodifiable
{
    use UnmodifiableContainerAA;

    #[\Override]
    public function offsetSet($offset, $value): void
    {
        throw new UnmodifiableException();
    }

    #[\Override]
    public function offsetUnset($offset): void
    {
        throw new UnmodifiableException();
    }

    #[\Override]
    public function offsetUnsetNode($offset): void
    {
        throw new UnmodifiableException();
    }

    #[\Override]
    public function copy(?Interpolator $interpolator = null): static
    {
        return $this;
    }
}
