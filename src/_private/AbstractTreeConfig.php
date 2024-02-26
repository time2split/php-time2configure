<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolation;
use Time2Split\Config\Interpolator;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Config\_private\TreeConfig\TreeStorage;
use Time2Split\Help\Optional;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class AbstractTreeConfig implements Configuration, TreeStorage, DelimitedKeys
{

    protected string $delimiter;

    protected Interpolator $interpolator;

    protected array $storage;

    private int $count;

    // ========================================================================
    protected function __construct(string $delimiter, Interpolator $interpolator, array $storage = [])
    {
        $this->delimiter = $delimiter;
        $this->interpolator = $interpolator;
        $this->storage = $storage;
        $this->count = 0;
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

    public function pathToOffset(array $path): string
    {
        return \implode($this->getKeyDelimiter(), $path);
    }

    public function getTreeStorage(): array
    {
        return $this->storage;
    }

    // ========================================================================
    private function explodePath(?string $key): array
    {
        if (! isset($key))
            return [];

        return \explode($this->delimiter, $key);
    }

    private function deduplicateOffsets(array $offsets): array
    {
        if (\count($offsets) < 2)
            return $offsets;

        $ret = [];
        \rsort($offsets);
        $k = \array_pop($offsets);
        $ret[] = $k;

        while (! empty($offsets)) {
            $kk = (string) \array_pop($offsets);

            if (! \str_starts_with($kk, $k)) {
                $ret[] = $kk;
                $k = $kk;
            }
        }
        return $ret;
    }

    private function makeUserPath($key)
    {
        return "$key{$this->delimiter}";
    }

    private function &followPath(array $path): mixed
    {
        return \Time2Split\Help\Arrays::follow($this->storage, $path, TreeConfigSpecial::absent);
    }

    private function &followOffset($offset): mixed
    {
        return $this->followPath($this->explodePath($offset));
    }

    private function getUpdateList($offset, $value): array
    {
        $path = $this->explodePath($offset);
        return \Time2Split\Help\Arrays::pathToRecursiveList($path, $value);
    }

    private function interpolate($value): mixed
    {
        if (! ($value instanceof Interpolation))
            return $value;

        return $this->interpolator->execute($value->compilation, $this);
    }

    // ========================================================================
    private function updateOnUnexists(&$data, $k, $v): void
    {
        $this->count ++;
        $data[$k] = $v;
    }

    private function setStoredValue($offset, $value): void
    {
        $update = $this->getUpdateList($offset, $value);
        \Time2Split\Help\Arrays::updateRecursive($update, $this->storage, $this->updateOnUnexists(...));
    }

    // For external/user access
    private function set($offset, $value): void
    {
        $offset = $this->makeUserPath($offset);
        $compilation = $this->interpolator->compile($value);

        if ($compilation->isPresent())
            $value = new Interpolation((string) $value, $compilation->get());

        $this->setStoredValue($offset, $value);
    }

    private function getWithoutInterpolation($offset): mixed
    {
        $offset = $this->makeUserPath($offset);
        $val = $this->followOffset($offset);

        if ($val === TreeConfigSpecial::absent)
            return TreeConfigSpecial::absent;
        if (\is_array($val) && ! \array_is_list($val))
            return $val[''];

        return $val;
    }

    private function get($offset): mixed
    {
        $val = $this->getWithoutInterpolation($offset);

        if ($val === TreeConfigSpecial::absent)
            return null;

        return $this->interpolate($val);
    }

    public function getOptional($offset, bool $interpolate = true): Optional
    {
        $val = $this->getWithoutInterpolation($offset);

        if ($val === TreeConfigSpecial::absent)
            return Optional::empty();

        return Optional::of($interpolate ? $this->interpolate($val) : $val);
    }

    public function isPresent($offset): bool
    {
        return $this->getWithoutInterpolation($offset) !== TreeConfigSpecial::absent;
    }

    private function unset($offset): void
    {
        $path = $this->explodePath($offset);
        $last = \array_pop($path);
        $val = &$this->followPath($path);

        if ($val !== TreeConfigSpecial::absent) {
            $this->count --;
            unset($val[$last]);
        }
    }

    // ========================================================================
    private function &createIfNotPresent($offset): mixed
    {
        $ref = [];
        $update = $this->getUpdateList($offset, null);

        \Time2Split\Help\Arrays::updateRecursive($update, $this->storage, //
        self::updateOnUnexists(...), //
        null, //
        function (&$aref) use (&$ref) {
            $ref[] = &$aref;
        });
        return $ref[0];
    }

    // Unused
    private function &getReference($offset): mixed
    {
        return $this->createIfNotPresent($offset);
    }

    // ========================================================================
    public function subConfig($offset): static
    {
        $val = $this->followOffset($offset);

        $ret = clone $this;
        $ret->storage = [];

        if ($val !== TreeConfigSpecial::absent)
            $ret->storage = $val;

        return $ret;
    }

    public function select($offset, ...$offsets): static
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
        return $this->isPresent($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
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
    private function generateKeyValuePairsOf(array $data): \Generator
    {
        foreach ($this->generateRawKeyValuePairsOf($data) as $k => $v) {
            yield $k => $this->interpolate($v);
        }
    }

    private function generateRawKeyValuePairsOf(array $data): \Generator
    {
        foreach ($data as $k => $v) {

            if (\is_array($v)) {

                if (\array_key_exists('', $v))
                    yield $k => $v[''];

                foreach ($this->generateKeyValuePairsOf($v) as $kk => $vv)
                    yield "$k{$this->delimiter}$kk" => $vv;
            }
        }
    }

    private function generateKeysOf(array $data): \Generator
    {
        foreach ($data as $k => $v) {

            if (\is_array($v)) {

                if (isset($v['']))
                    yield $k;

                foreach ($this->generateKeyValuePairsOf($v) as $kk => $NotUsed)
                    yield "$k{$this->delimiter}$kk";
            }
        }
        return;
        unset($NotUsed);
    }

    // ========================================================================
    public function getIterator(): \Generator
    {
        return self::generateKeyValuePairsOf($this->storage);
    }

    public function getRawValueIterator(): \Generator
    {
        return self::generateRawKeyValuePairsOf($this->storage);
    }
}