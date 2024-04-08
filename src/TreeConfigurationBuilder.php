<?php

declare(strict_types=1);

namespace Time2Split\Config;

use Time2Split\Config\_private\AbstractTreeConfig;
use Time2Split\Config\_private\TreeConfig;
use Time2Split\Help\Iterables;

/**
 * A builder of configuration instances.
 *
 * The builded configuration is a tree where each node may have a value.
 * That is, considering '.' as a path node delimiter, if $config['parent.child'] is an existant path,
 * then $config['parent'] may have it's own value without breaking the tree structure of the instance.
 * 
 * A builder is itself a configuration instance.
 * It's the only one that can modify its internal state (interpolator, key delimiter).
 * The {@see TreeConfigurationBuilder::build()} method can provides a more efficient configuration instances than the builder,
 * due to the immutability of the internal state of the created instance.
 *
 * A builder can only be created with the {@see Configurations::builder()} method factory.
 * 
 * @template K
 * @template V
 * @extends AbstractTreeConfig<K,V,mixed>
 * 
 * @author Olivier Rodriguez (zuri)
 * @see Configurations::builder()
 * @package time2configure\configuration
 */
final class TreeConfigurationBuilder extends AbstractTreeConfig
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

    /**
     * Gets a new builder instance using another interpolator.
     * 
     * @return static A new builder instance.
     * @see BaseConfiguration::copy()
     */
    public function copy(?Interpolator $interpolator = null): static
    {
        $ret = new self();
        $ret->setInterpolator($interpolator ?? $this->interpolator);
        $this->copyToAbstract($ret, $interpolator);
        return $ret;
    }

    // ========================================================================

    /**
     * Makes a copy of the configuration tree.
     *
     * @param Configuration $config
     *            The configuration to copy from.
     * @param Interpolator $interpolator The interpolator to use for the copy.
     *  - (null)
     *  If not set then the copy contains the interpolated value of the configuration tree and has'nt an interpolator.
     *  - (isset)           
     *  If set then the copy uses this interpolator on the raw base value to create a new interpolated configuration.
     *  Note that the interpolator may be the same as $config, in that case it means that the same interpolation is conserved.
     * 
     * @return self This builder.
     */
    public function copyOf(Configuration $config, Interpolator $interpolator = null): self
    {
        $this->emptyCopyOf($config);

        if (isset($interpolator)) {

            if ($interpolator != $this->interpolator) {
                $this->setInterpolator($interpolator);
                $this->merge($config->getBaseValueIterator());
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
     * Makes a copy of a configuration conserving its interpolator but not the entries.
     *
     * @param Configuration $config
     *            The configuration to copy from.
     * @return self This builder.
     */
    public function emptyCopyOf(Configuration $config): self
    {
        $config = Configurations::ensureDelimitedKeys($config);
        return $this->reset()
            ->setInterpolator($config->getInterpolator())
            ->setKeyDelimiter($config->getKeyDelimiter())
            ->clearContent();
    }

    // ========================================================================

    private function clearContent(): self
    {
        $this->clear();
        return $this;
    }

    // ========================================================================
    /**
     * Resets the builder to its instanciation state.
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
     * Sets the interpolator of the Configuration instance to build.
     * 
     * This method restore any Interpolation value already stored in the builder to its base value.
     * Then the new interpolator is applied to all the values of the builder.
     *
     * @param Interpolator $interpolator
     *            The interpolator to assign.
     * @return self This builder.
     */
    public function setInterpolator(?Interpolator $interpolator = null): self
    {
        if (!isset($interpolator))
            $interpolator = Interpolators::null();

        if ($this->interpolator == $interpolator)
            return $this;

        $this->interpolator = $interpolator;

        if ($this->count() > 0) {
            $it = $this->getRawValueIterator();
            $it = Iterables::mapValue($it, Entries::baseValueOf(...));

            foreach ($it as $k => $v)
                $this[$k] = $v;
        }
        return $this;
    }

    /**
     * Sets the key delimiter of the Configuration instance to build.
     *
     * An access key is composed of multiple parts defining a path in the Configuration instance tree.
     * The delimiter is a character that permits to split a key in parts defining the access path.
     */
    public function setKeyDelimiter(string $delimiter = '.'): self
    {
        $this->resetKeyDelimiter($delimiter);
        return $this;
    }

    // ========================================================================

    /**
     * Build a new configuration instance using the builder's tree and interpolator.
     *
     * @return Configuration The new instance
     */
    public function build(): Configuration
    {
        return TreeConfig::rawCopyOf($this);
    }
}
