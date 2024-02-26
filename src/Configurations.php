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

    public static function from(Configuration $config): Configuration
    {
        return self::builder()->from($config)->build();
    }

    public static function emptyFrom(Configuration $config): Configuration
    {
        return self::builder()->emptyFrom($config)->build();
    }

    public static function fromTree(array $tree): Configuration
    {
        return self::builder()->mergeTree($tree)->build();
    }

    // ========================================================================
    // INTERPOLATION
    // ========================================================================
    private static function getSourceValueOf($value): mixed
    {
        if ($value instanceof Interpolation)
            return $value->text;

        return $value;
    }

    public static function resetInterpolator(Configuration $config, Interpolator $interpolator): Configuration
    {
        $builder = Configurations::builder()->emptyFrom($config)->setInterpolator($interpolator);

        foreach ($config->getRawValueIterator() as $k => $v)
            $builder[$k] = self::getSourceValueOf($v);

        return $builder->build();
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
        return self::hierarchy($parent, self::emptyFrom($parent));
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