<?php
namespace Time2Split\Config\_private;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolation;
use Time2Split\Config\Interpolator;
use Time2Split\Config\TreeConfigBuilder;

/**
 * A TreeConfig is a hierarchical configuration in which its element can be accessed with a single key (eg.
 * $config[$key]) representing a path in the tree
 *
 * Each part of the path is delimited by an internal delimiter character.
 * Each node of the configuration can be set to a value.
 *
 *
 * @author zuri
 */
final class TreeConfig implements Configuration, \IteratorAggregate
{
    use ConfigUtilities;

    private string $delimiter;

    private Interpolator $interpolator;

    private array $data;

    // ========================================================================
    public function __construct(string $delimiter, Interpolator $interpolator)
    {
        $this->data = [];
        $this->delimiter = $delimiter;
        $this->interpolator = $interpolator;
    }

    // ========================================================================
    public function getInterpolator(): Interpolator
    {
        return $this->interpolator;
    }

    public function getKeyDelimiter(): string
    {
        return $this->delimiter;
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

    private function makeUserPath(string $key)
    {
        return "$key{$this->delimiter}";
    }

    private function &followPath(array $path): mixed
    {
        return \Time2Split\Help\Arrays::follow($this->data, $path, TreeConfigSpecial::absent);
    }

    private function &followOffset(string $offset): mixed
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

    private function getSourceValueOf($value): mixed
    {
        if ($value instanceof Interpolation)
            return $value->text;

        return $value;
    }

    // ========================================================================
    private static function updateOnUnexists(&$data, $k, $v): void
    {
        $data[$k] = $v;
    }

    private function setStoredValue($offset, $value): void
    {
        $update = $this->getUpdateList($offset, $value);
        \Time2Split\Help\Arrays::updateRecursive($update, $this->data, self::updateOnUnexists(...));
    }

    // For external/user access
    private function set($offset, $value): void
    {
        $offset = $this->makeUserPath($offset);
        $compilation = $this->interpolator->compile($value);

        if ($compilation->isPresent())
            $value = new Interpolation($value, $compilation->get());

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

    public function getOptional($offset): \Time2Split\Help\Optional
    {
        $val = $this->getWithoutInterpolation($offset);

        if ($val === TreeConfigSpecial::absent)
            return \Time2Split\Help\Optional::empty();

        return \Time2Split\Help\Optional::of($this->interpolate($val));
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

        if ($val !== TreeConfigSpecial::absent)
            unset($val[$last]);
    }

    // ========================================================================
    private function &createIfNotPresent($offset): mixed
    {
        $ref = [];
        $update = $this->getUpdateList($offset, null);

        \Time2Split\Help\Arrays::updateRecursive($update, $this->data, //
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
        $ret->data = [];

        if ($val !== TreeConfigSpecial::absent)
            $ret->data = $val;

        return $ret;
    }

    public function select($offset, ...$offsets): static
    {
        \array_unshift($offsets, $offset);
        $offsets = $this->deduplicateOffsets($offsets);

        $ret = clone $this;
        $ret->data = [];

        foreach ($offsets as $offset) {
            $val = $this->followOffset($offset);

            if ($val !== TreeConfigSpecial::absent)
                $ret->setStoredValue($offset, $val);
        }
        return $ret;
    }

    public function resetInterpolator(Interpolator $interpolator): static
    {
        $ret = TreeConfigBuilder::emptyOf($this)->setInterpolator($interpolator)->build();

        foreach ($this->traversableKeys() as $k)
            $ret[$k] = $this->getSourceValueOf($this->getWithoutInterpolation($k));

        return $ret;
    }

    public function clear(): void
    {
        $this->data = [];
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
    private function generateKeyValuePairsOf(array $data)
    {
        foreach ($data as $k => $v) {

            if (\is_array($v) && ! \array_is_list($v)) {

                if (\array_key_exists('', $v)) {
                    $val = $v[''];

                    if ($val !== TreeConfigSpecial::absent)
                        yield $k => $this->interpolate($val);
                }

                foreach ($this->generateKeyValuePairsOf($v) as $kk => $vv)
                    yield "$k{$this->delimiter}$kk" => $vv;
            }
        }
    }

    private function generateKeysOf(array $data)
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
    public function getIterator(): \Traversable
    {
        return self::generateKeyValuePairsOf($this->data);
    }

    public function traversableKeys(): \Traversable
    {
        return $this->generateKeysOf($this->data);
    }
}