<?php
declare(strict_types = 1);
namespace Time2Split\Config;

use Time2Split\Config\Entry\Consumer;
use Time2Split\Config\Entry\Map;
use Time2Split\Config\Entry\MapKey;
use Time2Split\Config\Entry\MapValue;
use Time2Split\Config\Entry\ReadingMode;
use Time2Split\Config\_private\TreeConfigHierarchy;
use Time2Split\Config\_private\Decorator\ConsumerDecorator;
use Time2Split\Config\_private\Decorator\MapDecorator;
use Time2Split\Config\_private\Decorator\UnmodifiableDecorator;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Help\Arrays;
use Time2Split\Help\Optional;
use Time2Split\Help\Set;
use Time2Split\Help\Sets;
use Time2Split\Help\Classes\NotInstanciable;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class Configurations
{
    use NotInstanciable;

    /**
     * Get a new TreeConfigBuilder instance.
     *
     * @return TreeConfigurationBuilder A new instance.
     */
    public static function builder(): TreeConfigurationBuilder
    {
        return new TreeConfigurationBuilder();
    }

    // ========================================================================
    // DECORATE
    // ========================================================================

    /**
     * Make a configuration unmodifiable.
     *
     * The unmodifiable behaviour is implemented as a decorator wrapping around the the base configuration,
     * that is if the base configuration may have updates outside the unmodifiable decorator.
     *
     * @param Configuration $config
     *            The configuration to wrap unmodifiable.
     * @return Configuration The unmodifiable instance.
     */
    public static function unmodifiable(Configuration $config): Configuration
    {
        if ($config instanceof UnmodifiableDecorator)
            return $config;

        return new UnmodifiableDecorator($config);
    }

    // ========================================================================
    // COPY
    // ========================================================================
    // TreeConfigBuilder facades

    /**
     * Make a copy of the configuration tree.
     *
     * @param Configuration $config
     *            The configuration to copy from.
     * @param Interpolator $resetInterpolator
     *            If not set (ie. null) the copy will contains the interpolated value of the configuration tree.
     *            If set the copy will use this interpolator on the raw base value to create a new interpolated configuration.$this
     *            Note that the interpolator may be the same as $config, in that case it means that the base interpolation is conserved.
     * @return self A new Configuration instance.
     */
    public static function treeCopyOf(Configuration $config, Interpolator $interpolator = null): Configuration
    {
        return self::builder()->copyOf($config, $interpolator)->build();
    }

    /**
     * Make a copy conserving the interpolation.
     *
     * @param Configuration $config
     *            The configuration to copy from.
     * @return self A new Configuration instance.
     */
    public static function rawTreeCopyOf(Configuration $config): Configuration
    {
        return self::treeCopyOf($config, $config->getInterpolator());
    }

    /**
     * Make a copy instance conserving the interpolator but not the values.
     *
     * @param Configuration $config
     *            The configuration to copy from.
     * @return self A new Configuration instance.
     */
    public static function emptyTreeCopyOf(Configuration $config): Configuration
    {
        return self::builder()->emptyCopyOf($config)->build();
    }

    /**
     * Make a configuration from trees-structured arrays.
     *
     * If some trees share some same branches then the last tree branches override the previous ones.
     *
     * @param array ...$trees
     *            The trees to consider.
     * @return Configuration A new Configuration instance.
     */
    public static function ofTree(array ...$trees): Configuration
    {
        return self::builder()->mergeTree(...$trees)->build();
    }

    // ========================================================================
    // HIERARCHY
    // ========================================================================

    /**
     * Create a new Configuration instance that inherit all data from $parent.
     * The $parent instance defines default values for the new Configuration instance that always exist.
     * The default values can be shadowed by that in the new instance.
     *
     * Note that the parent instance is assigned as it (ie. reference), that is it may always be modified outside the hierarchy instance.
     *
     * @param Configuration $parent
     *            The parent configuration to use.
     * @return Configuration A new Configuration instance where the parent tree is not modifiable.
     */
    public static function emptyChild(Configuration $parent): Configuration
    {
        return self::hierarchy($parent, self::emptyTreeCopyOf($parent));
    }

    /**
     * Create a new Configuration instance that inherit all data from a sequence of parents.
     * The [$parent,...$childs] instances defines default values for the new Configuration instance that always exist.
     * The default values can be shadowed by that in the new instance.
     *
     * The order of the parents in the sequence is signifiant: the last parents shadowed the previous in case of common branches.
     * Note that the parents instances are assigned as it (ie. references), that is they may always be modified outside the hierarchy instance.
     *
     * @param Configuration $parent
     *            The first parent of the sequence.
     * @param Configuration ...$childs
     *            More parents of the sequence.
     * @return Configuration A new Configuration hierarchy instance.
     */
    public static function hierarchy(Configuration $parent, Configuration ...$childs): Configuration
    {
        if (empty($childs))
            return $parent;

        if ($parent instanceof TreeConfigHierarchy)
            return $parent->append(...$childs);

        return new TreeConfigHierarchy($parent, ...$childs);
    }

    // ========================================================================
    // MERGING
    // ========================================================================

    /**
     * Merge all levels of tree-shaped data source within a Configuration instance destination.
     * The merge occurs recursively with the sub-data of the source.
     * If an array sub-data is a list, then the list is considered as a simple value and the recursion stop.
     */
    public static function mergeTree(Configuration&DelimitedKeys $dest, array ...$trees): void
    {
        foreach ($trees as $tree)
            Arrays::linearArrayRecursive($dest, $tree, $dest->pathToOffset(...));
    }

    /**
     * Copy the entries of a sequence data source into a Configuration instance destination,
     * that is copy all the key => value pairs from $src into $dest.
     * If an entry to copy is already present in the configuration then the entry is copied and override the previous entry.
     */
    public static function merge(Configuration $dest, iterable ...$sources): void
    {
        foreach ($sources as $src)
            foreach ($src as $k => $v)
                $dest[$k] = $v;
    }

    /**
     * Copy the entries of a sequence data source into a Configuration instance destination,
     * that is copy all the key => value pairs from $src into $dest.
     * If an entry to copy is already present in the configuration then the entry is not copied, the first entry stay in place.
     */
    public static function union(Configuration $dest, iterable ...$sources): void
    {
        foreach ($sources as $src)
            // @TODO maybe better with a method $dest->setIfAbsent()
            foreach ($src as $k => $v)
                if (! $dest->isPresent($k))
                    $dest[$k] = $v;
    }

    // ========================================================================
    // CONSUMING
    // ========================================================================

    /**
     * Decorate a configuration to do an action when a value is read (only with interpolation).
     *
     * @param Configuration $config
     *            The base configuration to decorate.
     * @param Consumer $do
     *            The action to do.
     * @return Configuration A new configuration instance wrapping the base configuration.
     */
    public static function doOnRead(Configuration $config, Consumer $do): Configuration
    {
        return new class($config, $do) extends ConsumerDecorator {

            public function offsetGet($key, ReadingMode $mode = ReadingMode::Normal): mixed
            {
                $v = $this->decorate->offsetGet($key, $mode);

                if ($mode === ReadingMode::Interpolate)
                    $this->consumer->consume($this->decorate, $key, $v);

                return $v;
            }

            public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Generator
            {
                if ($mode !== ReadingMode::Interpolate)
                    return $this->decorate->getIterator($mode);

                foreach ($this->decorate->getIterator($mode) as $k => $v) {
                    $this->offsetGet($k);
                    yield $k => $v;
                }
            }

            public function getOptional($offset, ReadingMode $mode = ReadingMode::Normal): Optional
            {
                $item = $this->decorate->getOptional($offset, $mode);

                if ($mode === ReadingMode::Interpolate)
                    $this->consumer->consume($this->decorate, $offset, $item->orElse(null));

                return $item;
            }
        };
    }

    // ========================================================================
    // MAPPING
    // ========================================================================
    private static Set $defaultModes;

    private static function getReadingModeSet(?iterable $list): Set
    {
        if ($list instanceof Set)
            return clone $list;

        $classInstance = ReadingMode::Normal;

        if (null === $list)
            return self::$defaultModes ??= Sets::ofBackedEnum($classInstance)->setMore(ReadingMode::Normal);

        return Sets::ofBackedEnum($classInstance)->setFromList($list);
    }

    /**
     * Decorate a configuration to do an mapping when a value is read.
     *
     * @param Configuration $config
     *            The base configuration to decorate.
     * @param Map $map
     *            The mapping to do.
     * @param ?iterable[ReadingMode] $mapOnReadingMode
     *            Only do the mapping for the specified {@link ReadingMode}s.
     *            If not set then the only allowed mode is {@link ReadingMode::Normal}
     * @return Configuration A new configuration instance wrapping the base configuration.
     */
    public static function mapOnRead(Configuration $config, MapValue $map, iterable $mapOnReadingMode = null): Configuration
    {
        return new class($config, $map, self::getReadingModeSet($mapOnReadingMode)) extends MapDecorator {

            public function __construct($decorate, $map, private readonly Set $mapOnReadingMode)
            {
                parent::__construct($decorate, $map);
            }

            public function offsetGet(mixed $key, ReadingMode $mode = ReadingMode::Normal): mixed
            {
                $v = $this->decorate->offsetGet($key, $mode);

                if ($this->mapOnReadingMode[$mode])
                    return $this->map->map($this->decorate, $key, $v)->value;
                else
                    return $v;
            }

            public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Iterator
            {
                if (! $this->mapOnReadingMode[$mode])
                    yield from $this->decorate->getIterator($mode);
                else {

                    foreach ($this->decorate->getIterator($mode) as $k => $notUsed)
                        yield $k => $this->offsetGet($k);

                    unset($notUsed);
                }
            }

            public function getOptional($key, ReadingMode $mode = ReadingMode::Normal): Optional
            {
                $item = $this->decorate->getOptional($key, $mode);

                if (! $this->mapOnReadingMode[$mode])
                    return $item;

                $value = $item->orElse(null);
                $entry = $this->map->map($this->decorate, $key, $value);
                return Optional::ofNullable($entry->value);
            }
        };
    }

    /**
     * Decorate a configuration to do a mapping on key => value entry before their assignment.
     *
     * @param Configuration $config
     *            The base configuration to decorate.
     * @param MapKey|MapValue $map
     *            The mapping to do on an entry before its storage.
     * @return Configuration A new configuration instance wrapping the base configuration.
     */
    public static function mapOnSet(Configuration $config, Map $map): Configuration
    {
        return new class($config, $map) extends MapDecorator {

            public function offsetSet($key, $value): void
            {
                $entry = $this->map->map($this->decorate, $key, $value);
                $this->decorate[$entry->key] = $entry->value;
            }
        };
    }

    /**
     * Decorate a configuration to do an action when an entry is unset (the entry may be absent).
     *
     * @param Configuration $config
     *            The base configuration to decorate.
     * @param Consumer $do
     *            The mapping to do.
     * @return Configuration A new configuration instance wrapping the base configuration.
     */
    public static function doOnUnset(Configuration $config, Consumer $do): Configuration
    {
        return new class($config, $do) extends ConsumerDecorator {

            public function offsetUnset($key): void
            {
                $this->consumer->consume($this->decorate, $key, $this->decorate[$key]);
                parent::offsetUnset($key);
            }

            public function clear(): void
            {
                foreach ($this->decorate as $key => $notUsed)
                    $this->consumer->consume($this->decorate, $key, $this->decorate[$key]);

                $this->decorate->clear();
                unset($notUsed);
            }
        };
    }
}