<?php
namespace Time2Split\Config;

/**
 * Extends the BaseConfiguration with utilities methods.
 *
 * @author zuri
 */
interface Configuration extends BaseConfiguration
{

    public function toArray(): array;

    public function merge(Configuration $config): void;

    public function union(Configuration $config): void;
}