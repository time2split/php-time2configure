<?php
declare(strict_types = 1);
namespace Time2Split\Config;

use Time2Split\Config\_private\AbstractTreeConfig;
use Time2Split\Config\_private\TreeConfig;
use Time2Split\Config\_private\TreeConfig\DelimitedKeys;
use Time2Split\Help\Traversables;

/**
 * A builder of tree-shaped Configuration instances.
 *
 * A builder can only be create with the Configurations::builder() method factory.
 *
 * The created Configuration is a tree where each node may have a value.
 * That is, considering '.' as a path node delimiter, if $config['parent.child'] is an existant path,
 * then $config['parent'] may have it's own value without breaking the tree structure of the instance.
 *
 * @author Olivier Rodriguez (zuri)
 * @see Configurations::builder()
 */
final class TreeConfigBuilder extends AbstractTreeConfig
{

    /**
     *
     * @internal By default the internal state of the builder is equivalent to call all the setters of the builder without any argument.
     *
     */
    public function __construct()
    {
        $this->interpolator = Interpolators::null();
        $this->reset();
    }

    public function copy(?Interpolator $interpolator = null): static
    {
        $ret = new self();
        $ret->setInterpolator($interpolator ?? $this->interpolator);
        $this->copyToAbstract($ret, $interpolator);
        return $ret;
    }

    // ========================================================================

    /**
     * Make a copy of the configuration tree.
     *
     * @param Configuration $config
     *            The configuration to copy from.
     * @param Interpolator $resetInterpolator
     *            If not set (ie. null) the copy will contains the interpolated value of the configuration tree.
     *            If set the copy will use this interpolator on the raw base value to create a new interpolated configuration.$this
     *            Note that the interpolator may be the same as $config, in that case it means that the base interpolation is conserved.
     * @return self This builder.
     */
    public function copyOf(Configuration $config, Interpolator $resetInterpolator = null): self
    {
        $this->emptyCopyOf($config);

        if (isset($resetInterpolator)) {

            if ($resetInterpolator != $this->interpolator) {
                $this->setInterpolator($resetInterpolator);
                $this->merge(Traversables::mapValue($config->getRawValueIterator(), self::getBaseValue(...)));
            } else
                $this->_rawCopy($config);
        } else
            $this->merge($config);

        return $this;
    }

    private function _rawCopy(Configuration $config): void
    {
        foreach ($config->getRawValueIterator() as $k => $v)
            $this[$k] = $v;
    }

    /**
     * Make a copy instance conserving the interpolator but not the values.
     *
     * @param Configuration $config
     *            The configuration to copy from.
     * @return self This builder.
     */
    public function emptyCopyOf(Configuration $config): self
    {
        return $this->reset()
            ->setInterpolator($config->getInterpolator())
            ->setKeyDelimiter($this->getKeyDelimiterOf($config))
            ->clearContent();
    }

    // ========================================================================
    private static function getKeyDelimiterOf(DelimitedKeys $config): ?string
    {
        return $config->getKeyDelimiter();
    }

    private function clearContent(): self
    {
        $this->clear();
        return $this;
    }

    // ========================================================================
    /**
     * Reset the builder to its construction state.
     *
     * @return self This builder
     */
    public function reset(): self
    {
        return $this->setKeyDelimiter()
            ->clearContent()
            ->setInterpolator();
    }

    /**
     * Set the interpolator of the Configuration instance to build.
     *
     * @param Interpolator $interpolator
     *            The interpolator to assign.
     * @return self This builder.
     */
    public function setInterpolator(?Interpolator $interpolator = null): self
    {
        if (! isset($interpolator))
            $interpolator = Interpolators::null();

        if ($this->interpolator == $interpolator)
            return $this;

        $this->interpolator = $interpolator;

        if ($this->count() > 0) {
            $it = $this->getRawValueIterator();
            $it = Traversables::mapValue($it, Entries::baseValueOf(...));

            foreach ($it as $k => $v)
                $this[$k] = $v;
        }
        return $this;
    }

    /**
     * Set the key delimiter of the Configuration instance to build.
     *
     * An access key is composed of multiple parts defining a path in the Configuration instance tree.
     * The delimiter is a character that permits to split a key in parts defining the access path.
     */
    public function setKeyDelimiter(string $delimiter = '.'): self
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    // ========================================================================

    /**
     * Build a new Configuration instance.
     *
     * @return Configuration The new instance
     */
    public function build(): Configuration
    {
        return TreeConfig::rawCopyOf($this);
    }
}