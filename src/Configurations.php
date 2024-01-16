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

    /**
     * Merge the first level of tree shaped data within a Configuration instance,
     * that is add all the key/value pairs from $config.
     *
     * @param array $config
     */
    public static function flatMerge(Configuration $config, array|\Traversable $data): void
    {
        foreach ($data as $k => $v)
            $config[$k] = $v;
    }

    private static function linearizePath(array $path, Configuration $config)
    {
        return \implode($config->getKeyDelimiter(), $path);
    }

    /**
     * Merge all levels of tree-shaped data within a Configuration instance,
     * The merge occurs recursively with the sub-data.
     * If an array sub-data is a list, then the list is considered as a simple value and the recursion stop.
     *
     * @param array $config
     *            The configuration data
     */
    public static function merge(Configuration $config, array|\Traversable $data): void
    {
        $linearize = fn ($path) => self::linearizePath($path, $config);

        if (\is_array($data))
            \Time2Split\Help\Arrays::linearArrayRecursive($config, $data, $linearize);
        else
            self::flatMerge($config, \iterator_to_array($data));
    }
}