<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Configuration;
use Time2Split\Config\Interpolator;
use Time2Split\Help\Optional;
use Time2Split\Config\Interpolation;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class ResetInterpolationDecorator extends Decorator
{

    public function __construct(Configuration $decorate, protected Interpolator $interpolator)
    {
        parent::__construct($decorate);
    }

    private function interpolate($value): mixed
    {
        if ($value instanceof Interpolation)
            $value = $value->text;

        $compilation = $this->interpolator->compile($value);

        if ($compilation->isPresent())
            $value = $this->interpolator->execute($compilation->get(), $this->decorate);

        return $value;
    }

    public function offsetGet(mixed $offset, bool $interpolate = true): mixed
    {
        return $this->interpolate($this->decorate->offsetGet($offset, false));
    }

    public function getOptional($offset, bool $interpolate = true): Optional
    {
        $opt = $this->decorate->getOptional($offset, false);

        if (! $opt->isPresent())
            return $opt;
        else
            return Optional::of($this->interpolate($opt->get()));
    }

    public function getIterator(bool $interpolate = true): \Iterator
    {
        if (! $interpolate)
            yield from $this->getRawValueIterator();
        else {
            foreach ($this->decorate->getRawValueIterator() as $k => $v)
                yield $k => $this->interpolate($v);
        }
    }
}