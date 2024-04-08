<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Configuration;
use Time2Split\Config\Entry\Map;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class MapDecorator extends Decorator
{

    public function __construct(Configuration $decorate, protected Map $map)
    {
        parent::__construct($decorate);
    }
}