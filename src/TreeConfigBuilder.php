<?php
namespace Time2Split\Config;

/**
 * A builder of tree-shaped Configuration instances.
 * The created Configuration is a tree where each node may have a value.
 * That is, considering '.' as a path node delimiter, if $config['parent.child'] is an existant path,
 * then $config['parent'] may have it's own value without breaking the tree structure of the instance.
 *
 * @author zuri
 *
 */
final class TreeConfigBuilder
{

    private string $delimiter;

    private array|\Traversable $content;

    private ?Interpolator $interpolator;

    private function __construct()
    {
        $this->reset();
    }

    /**
     * Get a new builder in its default state.
     *
     * By default the internal state of the builder is equivalent to call all the setters of the builder without any argument.
     *
     * @return TreeConfigBuilder The builder
     */
    public static function builder(): TreeConfigBuilder
    {
        return new TreeConfigBuilder();
    }

    /**
     * Get a builder which can create a copy of a Configuration instance
     *
     * @param Configuration $config
     *            The instance
     * @return self The builder
     */
    public static function of(Configuration $config): self
    {
        return self::emptyOf($config)->setContent(\iterator_to_array($config));
    }

    /**
     * Get a builder which can create an empty copy of a Configuration instance.
     *
     * @param Configuration $config
     *            The instance
     * @return self The builder
     */
    public static function emptyOf(Configuration $config): self
    {
        return (new self())->setDelimiter($config->getKeyDelimiter())
            ->setInterpolator($config->getInterpolator());
    }

    /**
     * Reset the builder to its construction state
     *
     * @return self This builder
     */
    public function reset(): self
    {
        return $this->setDelimiter()
            ->setContent()
            ->setInterpolator();
    }

    /**
     * Set the Interpolator of the Configuration instance to build.
     *
     * @param Interpolator $interpolator
     *            The interpolator
     * @return self This builder
     */
    public function setInterpolator(?Interpolator $interpolator = null): self
    {
        if (! isset($interpolator))
            $interpolator = Interpolators::null();

        $this->interpolator = $interpolator;
        return $this;
    }

    /**
     * Set the key delimiter of the Configuration instance to build.
     *
     * An access key is composed of multiple parts defining a path in the Configuration instance tree.
     * The delimiter is a character that permits to split a key in parts defining the access path.
     */
    public function setDelimiter(string $delimiter = '.'): self
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * Set the content of the Configuration instance to build.
     * The content is merged to the instance with the Configuration::merge() method.
     *
     * @param array|\Traversable $content
     *            The content
     * @return self This buider
     */
    public function setContent(array|\Traversable $content = []): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Build a new Configuration instance.
     *
     * @return Configuration The new instance
     */
    public function build(): Configuration
    {
        $ret = new _private\TreeConfig( //
        $this->delimiter, //
        $this->interpolator); //

        if (! empty($this->content))
            Configurations::mergeArrayRecursive($ret, $this->content);

        return $ret;
    }
}