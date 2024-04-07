<?php

declare(strict_types=1);

namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolation;
use Time2Split\Config\Interpolator;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Config\_private\TreeConfig\TreeStorage;
use Time2Split\Help\Optional;
use Time2Split\Config\Entries;
use Time2Split\Config\Entry\ReadingMode;
use Time2Split\Help\Iterables;
use Time2Split\Help\IterableTrees;

/**
 *
 * @internal
 * 
 * @template K
 * @template V
 * @template I
 * @extends Configuration<K,V>
 * @implements TreeStorage<K,V>
 * 
 * @author Olivier Rodriguez (zuri)
 */
abstract class AbstractTreeConfig extends Configuration implements TreeStorage, DelimitedKeys
{

    /**
     * @var non-empty-string
     */
    protected string $delimiter;

    /**
     * @var Interpolator<V,I>
     */
    protected Interpolator $interpolator;

    /**
     * @var array<K,V>
     */
    protected array $storage;

    private int $count;

    // ========================================================================

    /**
     * @param Interpolator<V,I> $interpolator
     * @param array<K,V> $storage
     */
    protected function __construct(string $delimiter, Interpolator $interpolator, array $storage = [])
    {
        $this->resetKeyDelimiter($delimiter);
        $this->interpolator = $interpolator;
        $this->storage = $storage;
        $this->count = 0;
    }

    protected function copyToAbstract(AbstractTreeConfig $dest, ?Interpolator $resetInterpolator): void
    {
        assert(0 === $dest->count());

        if (isset($resetInterpolator)) {
            assert($resetInterpolator === $dest->interpolator);

            if ($resetInterpolator != $this->interpolator)
                $dest->merge(Iterables::mapValue($this->getRawValueIterator(), Entries::baseValueOf(...)));
            else
                $dest->storage = $this->storage;
        } else {
            assert($this->interpolator === $dest->interpolator);
            $dest->merge($this);
        }
    }

    // ========================================================================
    public function getInterpolator(): Interpolator
    {
        return $this->interpolator;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function getKeyDelimiter(): string
    {
        return $this->delimiter;
    }

    protected function resetKeyDelimiter(string $delimiter = '.'): void
    {
        if (empty($delimiter))
            throw new \InvalidArgumentException('Delimiter must be a non-empty-string');

        $this->delimiter = $delimiter;
    }

    /**
     * @param array<mixed> $path
     */
    public function pathToOffset(array $path): string
    {
        return \implode($this->getKeyDelimiter(), $path);
    }

    /**
     * @return array<K,V>
     */
    public function getTreeStorage(): array
    {
        return $this->storage;
    }

    // ========================================================================

    /**
     * @return array<int,string>
     */
    private function explodePath(?string $key): array
    {
        if (!isset($key))
            return [];

        return \explode($this->delimiter, $key);
    }

    /**
     * @param array<string> $offsets
     * @return array<int,string>
     */
    private function deduplicateOffsets(array $offsets): array
    {
        if (\count($offsets) < 2)
            return $offsets;

        $ret = [];
        \rsort($offsets);
        $k = \array_pop($offsets);
        $ret[] = $k;

        while (!empty($offsets)) {
            $kk = (string) \array_pop($offsets);

            if (!\str_starts_with($kk, $k)) {
                $ret[] = $kk;
                $k = $kk;
            }
        }
        return $ret;
    }

    /**
     * @param ?K $key
     */
    private function makeUserPath($key): string
    {
        return "$key{$this->delimiter}";
    }

    /**
     * @param array<int,string> $path
     */
    private function &followPath(array $path): mixed
    {
        return IterableTrees::follow($this->storage, $path, TreeConfigSpecial::absent);
    }

    private function &followOffset(mixed $offset): mixed
    {
        return $this->followPath($this->explodePath($offset));
    }

    /**
     * @param V|Interpolation<V>|null $value
     * @return array<int,mixed>
     */
    private function getUpdateList(mixed $offset, $value): array
    {
        return $this->explodePath($offset);
    }

    // ========================================================================

    /**
     * @param V|Interpolation<V> $value
     */
    private function setStoredValue(mixed $offset, $value): void
    {
        $leaf = &$this->createIfNotPresent($offset);
        $leaf = $value;
    }

    /**
     * @param ?K $offset
     * @param V $value
     */
    // For external/user access
    private function set($offset, $value): void
    {
        $offset = $this->makeUserPath($offset);
        $compilation = $this->interpolator->compile($value);

        if ($compilation->isPresent())
            $value = new Interpolation($value, $compilation->get());

        $this->setStoredValue($offset, $value);
    }

    /**
     * @param ?K $offset
     */
    private function getRawValue($offset): mixed
    {
        $offset = $this->makeUserPath($offset);
        return $this->followOffset($offset);
    }

    /**
     * @param ?K $offset
     */
    private function get($offset, ReadingMode $mode = ReadingMode::Normal): mixed
    {
        $rawValue = $this->getRawValue($offset);

        if ($rawValue === TreeConfigSpecial::absent)
            return null;

        return Entries::valueOf($rawValue, $this, $mode);
    }

    /**
     * @param ?K $offset
     */
    public function getOptional($offset, ReadingMode $mode = ReadingMode::Normal): Optional
    {
        $rawValue = $this->getRawValue($offset);

        if ($rawValue === TreeConfigSpecial::absent)
            /** @var Optional<V> */
            return Optional::empty();

        return Optional::of(Entries::valueOf($rawValue, $this, $mode));
    }

    public function isPresent($offset): bool
    {
        return $this->getRawValue($offset) !== TreeConfigSpecial::absent;
    }

    public function nodeIsPresent($offset): bool
    {
        return $this->followOffset($offset) !== TreeConfigSpecial::absent;
    }

    /**
     * @param ?K $offset
     */
    private function unset($offset): void
    {
        $path = $this->explodePath($offset);
        $val = &$this->followPath($path);

        if (\is_array($val) && \array_key_exists('', $val)) {
            $this->count--;
            unset($val['']);
        }
    }

    public function removeNode($offset): void
    {
        $path = $this->explodePath($offset);

        if (empty($path))
            return;

        $last = \array_pop($path);
        $val = &$this->followPath($path);

        if (\is_array($val) && \array_key_exists($last, $val)) {
            $this->count -= IterableTrees::countLeaves($val[$last]);
            unset($val[$last]);
        }
    }

    // ========================================================================


    /**
     * @param K $offset
     */
    private function &createIfNotPresent($offset): mixed
    {
        $def = (object)[];
        $update = $this->getUpdateList($offset, null);
        $ref = &IterableTrees::follow($this->storage, $update, $def);

        if ($ref !== $def)
            return $ref;

        unset($ref);
        $ref = [null];
        $this->count++;

        IterableTrees::setBranch(
            $this->storage,
            $update,
            setLeaf: function (&$leaf) use (&$ref) {
                $ref = [&$leaf];
            },
        );
        return $ref[0];
    }

    /**
     * @param K $offset
     */
    private function &getReference($offset): mixed
    {
        return $this->createIfNotPresent($offset);
    }

    // ========================================================================
    public function subTreeView($offset): static
    {
        $ref = &$this->getReference($offset);
        $ref = [];
        $ret = clone $this;
        $ret->storage = &$ref;
        return $ret;
    }

    public function subTreeCopy($offset): static
    {
        $val = $this->followOffset($offset);

        $ret = clone $this;
        $ret->storage = [];

        if ($val !== TreeConfigSpecial::absent)
            $ret->storage = $val;

        return $ret;
    }

    public function copyBranches($offset, ...$offsets): static
    {
        \array_unshift($offsets, $offset);
        $offsets = $this->deduplicateOffsets($offsets);

        $ret = clone $this;
        $ret->storage = [];

        foreach ($offsets as $offset) {
            $val = $this->followOffset($offset);

            if ($val !== TreeConfigSpecial::absent)
                $ret->setStoredValue($offset, $val);
        }
        return $ret;
    }

    public function clear(): void
    {
        $this->count = 0;
        $this->storage = [];
    }

    // ========================================================================
    public function offsetExists($offset): bool
    {
        $opt = $this->getOptional($offset);
        return $opt->isPresent() && null !== $opt->get();
    }

    public function offsetGet($offset, ReadingMode $mode = ReadingMode::Normal): mixed
    {
        return $this->get($offset, $mode);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->unset($offset);
    }

    // ========================================================================

    /**
     * Retrieves the raw entries of the configuration.
     *
     * @return \Iterator<K,V|Interpolation<V>> The entry sequence of the configuration.
     */
    protected function getRawEntries(): \Iterator
    {
        return $this->rawEntriesOf($this->storage);
    }

    /**
     * @param array<K,V|Interpolation<V>> $data
     * @return \Iterator<K,V|Interpolation<V>>
     */
    private function rawEntriesOf(array $data): \Iterator
    {
        /** @var array<K,V|Interpolation<V>> $v*/
        foreach ($data as $k => $v) {

            if (\is_array($v)) {

                if (\array_key_exists('', $v))
                    yield $k => $v[''];

                foreach ($this->rawEntriesOf($v) as $kk => $vv)
                    yield "$k{$this->delimiter}$kk" => $vv;
            }
        }
    }

    /**
     * 
     * @internal It uses {@link AbstractTreeConfig::getRawEntries()} to retrieve the raw entries.
     * 
     * @return \Iterator<K,V|Interpolation<V>>
     * 
     * @see \Time2Split\Config\BaseConfiguration::getIterator()
     */
    final public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Iterator
    {
        return Entries::entriesOf($this->getRawEntries(), $this, $mode);
    }
}
