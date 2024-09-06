<?php

declare(strict_types=1);

namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolator;
use Time2Split\Config\Entry\ReadingMode;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Config\Configurations;
use Time2Split\Help\Iterables;
use Time2Split\Help\Optional;

/**
 * A sequence of TreeConfig instances where the the last one is the only mutable instance.
 *
 * @template K
 * @template V
 * @extends Configuration<K,V>
 * @implements \IteratorAggregate<K,V>
 *  
 * @author Olivier Rodriguez (zuri)
 */
final class TreeConfigHierarchy extends Configuration implements \IteratorAggregate, DelimitedKeys
{

    /**
     * The first element is the last TreeConfig added.
     *
     * @var (Configuration<mixed,mixed>&DelimitedKeys)[]
     */
    private array $rlist;

    // ========================================================================
    public function __construct(Configuration ...$list)
    {
        $delims = [];

        $list = \array_map(Configurations::ensureDelimitedKeys(...), $list);
        $udelims = \Time2Split\Help\Arrays::arrayMapUnique(fn ($i) => $i->getKeyDelimiter(), $list);

        if (\count($udelims) > 1)
            throw new \Error("Has multiple delimiters: " . print_r($delims, true));

        $this->rlist = \array_reverse($list);
    }

    /**
     * @param array<Configuration<mixed,mixed>&DelimitedKeys> $list
     */
    private function resetRList(array $list): static
    {
        $ret = new self();
        $ret->rlist = $list;
        return $ret;
    }

    public function append(Configuration ...$childs): static
    {
        $list = \array_reverse($this->rlist);
        $list = \array_merge($list, $childs);
        return new self(...$list);
    }

    public function __clone(): void
    {
        $last = $this->rlist[0];
        $this->rlist[0] = clone $last;
    }

    public function copy(?Interpolator $interpolator = null): static
    {
        $ret = new self(...$this->rlist);
        $ref = &$ret->rlist[0];
        $ref = $ref->copy($interpolator);

        for ($i = 1, $c = \count($this->rlist); $i < $c; $i++) {
            $ref = &$ret->rlist[$i];
            $ref = $ref->copy($interpolator);
        }
        return $ret;
    }

    // ========================================================================
    private function last(): Configuration&DelimitedKeys
    {
        return \Time2Split\Help\Arrays::firstValue($this->rlist);
    }

    public function getInterpolator(): Interpolator
    {
        return $this->last()->getInterpolator();
    }

    public function getKeyDelimiter(): string
    {
        return $this->last()->getKeyDelimiter();
    }

    public function pathToOffset(array $path): string
    {
        return $this->last()->pathToOffset($path);
    }

    public function count(): int
    {
        return Iterables::count($this);
    }

    // ========================================================================
    public function subTreeView($offset): static
    {
        $sub = [];
        foreach ($this->rlist as $c)
            $sub[] = $c->subTreeView($offset);

        return $this->resetRList($sub);
    }

    public function subTreeCopy($offset): static
    {
        $sub = [];
        foreach ($this->rlist as $c)
            $sub[] = $c->subTreeCopy($offset);

        return $this->resetRList($sub);
    }

    public function copyBranches($offset, ...$offsets): static
    {
        $offsets = [$offset, ...$offsets];

        $sub = [];
        foreach ($this->rlist as $c)
            $sub[] = $c->copyBranches(...$offsets);

        return $this->resetRList($sub);
    }

    /**
     * @param ?K $offset
     */
    private function get($offset, ReadingMode $mode = ReadingMode::Normal): mixed
    {
        foreach ($this->rlist as $c) {
            $v = $c->getOptional($offset, $mode);

            if ($v->isPresent())
                return $v->get();
        }
        return null;
    }

    public function getOptional($offset, ReadingMode $mode = ReadingMode::Normal): Optional
    {
        foreach ($this->rlist as $c) {
            $opt = $c->getOptional($offset, $mode);

            if ($opt->isPresent())
                return $opt;
        }
        /** @var Optional<V> */
        return Optional::empty();
    }

    public function isPresent($offset): bool
    {
        foreach ($this->rlist as $c) {

            if ($c->isPresent($offset))
                return true;
        }
        return false;
    }

    public function nodeIsPresent($offset): bool
    {
        foreach ($this->rlist as $c) {

            if ($c->nodeIsPresent($offset))
                return true;
        }
        return false;
    }

    public function clear(): void
    {
        $this->last()->clear();
    }

    // ========================================================================
    public function offsetExists($offset): bool
    {
        foreach ($this->rlist as $c) {

            if ($c->offsetExists($offset))
                return true;
        }
        return false;
    }

    public function offsetGet($offset, ReadingMode $mode = ReadingMode::Normal): mixed
    {
        return $this->get($offset, $mode);
    }

    public function offsetSet($offset, $value): void
    {
        $this->last()->offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->last()->offsetUnset($offset);
    }

    public function removeNode($offset): void
    {
        $this->last()->removeNode($offset);
    }

    // ========================================================================
    public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Generator
    {
        $cache = [];

        foreach ($this->rlist as $config) {
            foreach ($config->getIterator($mode) as $k => $v) {

                if (!isset($cache[$k])) {
                    $cache[$k] = true;
                    yield $k => $v;
                }
            }
        }
    }
}
