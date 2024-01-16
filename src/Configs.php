<?php
namespace Time2Split\Config;

use Time2Split\Config\_private\TreeConfigHierarchy;

final class Configs
{
    use \Time2Split\Help\Classes\NotInstanciable;

    public static function empty(): IConfig
    {
        return TreeConfigBuilder::builder()->build();
    }

    public static function of(IConfig $config): IConfig
    {
        return TreeConfigBuilder::of($config)->build();
    }

    public static function emptyOf(IConfig $config): IConfig
    {
        return TreeConfigBuilder::emptyOf($config)->build();
    }

    /**
     * Create a new IConfig instance that inherit all data from $parent.
     * The $parent instance defines default values for the new IConfig instance that always exist.
     * The default values can be shadowed by that in the new instance.
     */
    public static function emptyChild(IConfig $parent): IConfig
    {
        $child = TreeConfigBuilder::emptyOf($parent)->build();

        if ($parent instanceof TreeConfigHierarchy)
            return $parent->append($child);

        return new TreeConfigHierarchy($parent, $child);
    }

    // ========================================================================

    /**
     * Merge the first level of a $config,
     * that is add all the key/value pairs from $config.
     *
     * @param array $config
     */
    public static function flatMerge(IConfig $config, array|\Traversable $array): void
    {
        foreach ($array as $k => $v)
            $config[$k] = $v;
    }

    private static function linearizePath(array $path, IConfig $config)
    {
        return \implode($config->getKeyDelimiter(), $path);
    }

    /**
     * Merge some configuration data.
     * The merge occurs recursively with sub-data.
     * If a sub-data is a list, then the list is considered as a simple value and the recursion stop.
     *
     * @param array $config
     *            The configuration data
     */
    public static function merge(IConfig $config, array|\Traversable $data): void
    {
        $linearize = fn ($path) => self::linearizePath($path, $config);

        if (\is_array($data))
            \Time2Split\Help\Arrays::linearArrayRecursive($config, $data, $linearize);
        else
            self::flatMerge($config, \iterator_to_array($data));
    }
}