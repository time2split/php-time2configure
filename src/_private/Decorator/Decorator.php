<?php

declare(strict_types=1);

namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolator;
use Time2Split\Config\Entry\ReadingMode;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Config\Configurations;
use Time2Split\Help\Optional;

/**
 * @template K
 * @template V
 * @extends Configuration<K,V>
 * 
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class Decorator extends Configuration implements DelimitedKeys
{

    /**
     * @var Configuration<K,V>&DelimitedKeys
     */
    protected Configuration&DelimitedKeys $decorate;

    public function __construct(Configuration $decorate)
    {
        $this->decorate = Configurations::ensureDelimitedKeys($decorate);
    }

    public function copy(?Interpolator $interpolator = null): static
    {
        return $this->resetDecoration($this->decorate->copy($interpolator));
    }

    public function __clone()
    {
        $this->decorate = clone $this->decorate;
    }

    public function toArrayTree(int|string $leafKey = null, ReadingMode $mode = ReadingMode::Normal): array
    {
        return $this->decorate->toArrayTree($leafKey, $mode);
    }

    public function getDecorated(): Configuration
    {
        return $this->decorate;
    }

    public function getKeyDelimiter(): string
    {
        return $this->decorate->getKeyDelimiter();
    }

    public function pathToOffset(array $path): mixed
    {
        return $this->decorate->pathToOffset($path);
    }

    private function resetDecoration(Configuration $decorate): static
    {
        $ret = clone $this;
        $ret->decorate = Configurations::ensureDelimitedKeys($decorate);
        return $ret;
    }

    // ========================================================================
    // BaseConfig
    // ========================================================================
    public function getInterpolator(): Interpolator
    {
        return $this->decorate->getInterpolator();
    }

    public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Iterator
    {
        return $this->decorate->getIterator($mode);
    }

    public function getOptional($offset, ReadingMode $mode = ReadingMode::Normal): Optional
    {
        return $this->decorate->getOptional($offset, $mode);
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

    public function offsetGet(mixed $offset, ReadingMode $mode = ReadingMode::Normal): mixed
    {
        return $this->decorate->offsetGet($offset, $mode);
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
        return $this->resetDecoration($this->decorate->subTreeCopy($offset));
    }

    public function subTreeView($offset): static
    {
        return $this->resetDecoration($this->decorate->subTreeView($offset));
    }

    public function copyBranches($offset, ...$offsets): static
    {
        return $this->resetDecoration($this->decorate->copyBranches($offset, ...$offsets));
    }

    public function offsetUnsetNode($offset): void
    {
        $this->decorate->offsetUnsetNode($offset);
    }
}
