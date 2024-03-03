<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolator;
use Time2Split\Help\Optional;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class Decorator implements Configuration
{

    protected Configuration $decorate;

    public function __construct(Configuration $decorate)
    {
        $this->decorate = $decorate;
    }

    public function __clone()
    {
        $this->decorate = clone $this->decorate;
    }

    public function getDecorated(): Configuration
    {
        return $this->decorate;
    }

    private function resetDecoration(Configuration $decorate): static
    {
        $ret = clone $this;
        $ret->decorate = $decorate;
        return $ret;
    }

    // ========================================================================
    // BaseConfig
    // ========================================================================
    public function getInterpolator(): Interpolator
    {
        return $this->decorate->getInterpolator();
    }

    public function getIterator(): \Iterator
    {
        return $this->decorate->getIterator();
    }

    public function getRawValueIterator(): \Iterator
    {
        return $this->decorate->getRawValueIterator();
    }

    public function getOptional($offset, bool $interpolate = true): Optional
    {
        return $this->decorate->getOptional($offset, $interpolate);
    }

    public function isPresent($offset): bool
    {
        return $this->decorate->isPresent($offset);
    }

    public function clear(): void
    {
        $this->decorate->clear();
    }

    public function count(): int
    {
        return $this->decorate->count();
    }

    public function offsetSet($offset, $value): void
    {
        $this->decorate->offsetSet($offset, $value);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->decorate->offsetGet($offset);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->decorate->offsetExists($offset);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->decorate->offsetUnset($offset);
    }

    // ========================================================================
    // TreeConfiguration
    // ========================================================================
    public function nodeIsPresent($offset): bool
    {
        return $this->decorate->nodeIsPresent($offset);
    }

    public function subTreeCopy($offset): static
    {
        return $this->decorate->isPresent($offset);
    }

    public function subTreeView($offset): static
    {
        return $this->resetDecoration($this->decorate->subTreeView($offset));
    }

    public function copyBranches($offset, ...$offsets): static
    {
        return $this->resetDecoration($this->decorate->copyBranches($offset, ...$offsets));
    }

    public function removeNode($offset): void
    {
        $this->decorate->removeNode($offset);
    }

    // ========================================================================
    // Configuration
    // ========================================================================
    public function toArray(): array
    {
        return $this->decorate->toArray();
    }

    public function mergeTree(array ...$trees): static
    {
        $this->decorate->mergeTree(...$trees);
        return $this;
    }

    public function merge(iterable ...$configs): static
    {
        $this->decorate->merge(...$configs);
        return $this;
    }

    public function union(iterable ...$configs): static
    {
        $this->decorate->union(...$configs);
        return $this;
    }

    public function copy(?Interpolator $interpolator = null): self
    {
        return $this->resetDecoration($this->decorate->copy($interpolator));
    }

    public function unsetFluent(...$offsets): static
    {
        $this->decorate->unsetFluent(...$offsets);
        return $this;
    }

    public function removeNodeFluent(...$offsets): static
    {
        $this->decorate->removeNodeFluent(...$offsets);
        return $this;
    }
}