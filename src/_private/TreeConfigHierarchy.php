<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolation;
use Time2Split\Config\Interpolator;
use Time2Split\Help\Optional;
use Time2Split\Help\Traversables;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;

/**
 * A sequence of TreeConfig instances where the the last one is the only mutable instance.
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class TreeConfigHierarchy implements Configuration, \IteratorAggregate, DelimitedKeys
{
    use ConfigUtilities;

    /**
     * The first element is the last TreeConfig added.
     */
    private array $rlist;

    // ========================================================================
    public function __construct(Configuration&DelimitedKeys ...$list)
    {
        $delims = [];

        $udelims = \Time2Split\Help\Arrays::map_unique(fn ($i) => $i->getKeyDelimiter(), $list);

        if (\count($udelims) > 1)
            throw new \Error("Has multiple delimiters: " . print_r($delims, true));

        $this->rlist = \array_reverse($list);
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

    // ========================================================================
    private function last(): Configuration
    {
        return \Time2Split\Help\Arrays::first($this->rlist);
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
        return Traversables::count($this);
    }

    // ========================================================================
    public function subConfig($offset): static
    {
        $sub = [];
        foreach ($this->rlist as $c)
            $sub[] = $c->subConfig($offset);

        return new self(...$sub);
    }

    public function select(...$offset): static
    {
        $sub = [];
        foreach ($this->rlist as $c)
            $sub[] = $c->select(...$offset);

        return new self(...$sub);
    }

    private function get($offset): mixed
    {
        foreach ($this->rlist as $c) {
            $v = $c->getOptional($offset, false);

            if ($v->isPresent()) {
                $v = $v->get();

                if ($v instanceof Interpolation)
                    return $c->getInterpolator()->execute($v->compilation, $this);

                return $v;
            }
        }
        return null;
    }

    public function getOptional($offset, bool $interpolate = true): Optional
    {
        foreach ($this->rlist as $c) {
            $v = $c->getOptional($offset, $interpolate);

            if ($v->isPresent())
                return $v;
        }
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

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
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
    private function _getIterator(bool $raw): \Generator
    {
        $cache = [];
        $getIterator = $raw ? fn ($c) => $c->getRawValueIterator() : fn ($c) => $c;

        foreach ($this->rlist as $config) {
            foreach ($getIterator($config) as $k => $v) {

                if (! isset($cache[$k])) {
                    $cache[$k] = true;
                    yield $k => $v;
                }
            }
        }
    }

    public function getIterator(): \Generator
    {
        return $this->_getIterator(false);
    }

    public function getRawValueIterator(): \Generator
    {
        return $this->_getIterator(true);
    }
}