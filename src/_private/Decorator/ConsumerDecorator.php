<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Configuration;
use Time2Split\Config\Entry\Consumer;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class ConsumerDecorator extends Decorator
{

    public function __construct(Configuration $decorate, protected Consumer $consumer)
    {
        parent::__construct($decorate);
    }
}