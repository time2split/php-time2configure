<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolation;
use Time2Split\Config\Interpolator;
use Time2Split\Help\Optional;

/**
 * A sequence of TreeConfig instances where the the last one is the only mutable instance.
 *
 * @author zuri
 */
final class TreeConfigHierarchy implements Configuration, \IteratorAggregate
{
    use ConfigUtilities;

    /**
     * The first element is the last TreeConfig added.
     */
    private array $rlist;

    // ========================================================================
    public function __construct(Configuration ...$list)
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

    public function resetInterpolator(Interpolator $interpolator): static
    {
        $list = $this->rlist;
        $child = $this->last()->resetInterpolator($interpolator);
        $list[0] = $child;
        $list = \array_reverse($list);
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

    // ========================================================================
    public function traversableKeys(): \Traversable
    {
        $cache = [];

        foreach ($this->rlist as $config) {
            foreach ($config->traversableKeys() as $k) {

                if (! isset($cache[$k])) {
                    $cache[$k] = true;
                    yield $k;
                }
            }
        }
    }

    public function getIterator(): \Traversable
    {
        $cache = [];

        foreach ($this->rlist as $config) {
            foreach ($config as $k => $v) {

                if (! isset($cache[$k])) {
                    $cache[$k] = true;
                    yield $k => $v;
                }
            }
        }
    }
}