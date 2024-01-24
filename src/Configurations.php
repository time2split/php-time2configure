<?php
namespace Time2Split\Config;

use Time2Split\Config\_private\TreeConfigHierarchy;
use Time2Split\Help\Classes\NotInstanciable;

final class Configurations
{
    use NotInstanciable;

    public static function empty(): Configuration
    {
        return TreeConfigBuilder::builder()->build();
    }

    public static function of(Configuration $config): Configuration
    {
        return TreeConfigBuilder::of($config)->build();
    }

    public static function emptyOf(Configuration $config): Configuration
    {
        return TreeConfigBuilder::emptyOf($config)->build();
    }

    /**
     * Create a new Configuration instance that inherit all data from $parent.
     * The $parent instance defines default values for the new Configuration instance that always exist.
     * The default values can be shadowed by that in the new instance.
     */
    public static function emptyChild(Configuration $parent): Configuration
    {
        $child = TreeConfigBuilder::emptyOf($parent)->build();

        if ($parent instanceof TreeConfigHierarchy)
            return $parent->append($child);

        return new TreeConfigHierarchy($parent, $child);
    }

    // ========================================================================
    private static function linearizePath(array $path, Configuration $config)
    {
        return \implode($config->getKeyDelimiter(), $path);
    }

    /**
     * Merge all levels of tree-shaped data source within a Configuration instance destination.
     * The merge occurs recursively with the sub-data of the source.
     * If an array sub-data is a list, then the list is considered as a simple value and the recursion stop.
     */
    public static function mergeArrayRecursive(Configuration $dest, array $src): void
    {
        $linearize = fn ($path) => self::linearizePath($path, $dest);
        \Time2Split\Help\Arrays::linearArrayRecursive($dest, $src, $linearize);
    }

    /**
     * Copy the items of a sequence data source into a Configuration instance destination,
     * that is copy all the key => value pairs from $src into $dest.
     */
    public static function mergeTraversable(Configuration $dest, array|\Traversable $src): void
    {
        foreach ($src as $k => $v)
            $dest[$k] = $v;
    }

    public static function merge(Configuration $dest, Configuration $src): void
    {
        self::mergeTraversable($dest, $src->toArray());
    }

    public static function union(Configuration $dest, Configuration $src): void
    {
        // @TODO better with a method $dest->setIfAbsent()
        foreach ($src as $k => $v)
            if (! $dest->isPresent($k))
                $dest[$k] = $v;
    }
}