<?php
declare(strict_types = 1);
namespace Time2Split\Config;

use Time2Split\Config\_private\TreeConfigHierarchy;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Help\Arrays;
use Time2Split\Help\Classes\NotInstanciable;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class Configurations
{
    use NotInstanciable;

    public static function builder(): TreeConfigBuilder
    {
        return TreeConfigBuilder::_private_builder();
    }

    // ========================================================================
    // COPY
    // ========================================================================
    public static function copyOf(Configuration $config, Interpolator $interpolator = null): Configuration
    {
        return self::builder()->copyOf($config, $interpolator)->build();
    }

    public static function rawCopyOf(Configuration $config): Configuration
    {
        return self::builder()->rawCopyOf($config)->build();
    }

    public static function emptyCopyOf(Configuration $config): Configuration
    {
        return self::builder()->emptyCopyOf($config)->build();
    }

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
     */
    public static function emptyChild(Configuration $parent): Configuration
    {
        return self::hierarchy($parent, self::emptyCopyOf($parent));
    }

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
     */
    public static function merge(Configuration $dest, iterable ...$sources): void
    {
        foreach ($sources as $src)
            foreach ($src as $k => $v)
                $dest[$k] = $v;
    }

    public static function union(Configuration $dest, iterable ...$sources): void
    {
        foreach ($sources as $src)
            // @TODO maybe better with a method $dest->setIfAbsent()
            foreach ($src as $k => $v)
                if (! $dest->isPresent($k))
                    $dest[$k] = $v;
    }
}