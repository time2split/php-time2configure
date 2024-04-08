<?php
declare(strict_types = 1);
namespace Time2Split\Config\_private\Decorator;

use Time2Split\Config\Configuration;
use Time2Split\Config\Entries;
use Time2Split\Config\Interpolator;
use Time2Split\Config\Entry\ReadingMode;
use Time2Split\Help\Optional;

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

    public function getInterpolator(): Interpolator
    {
        return $this->interpolator;
    }

    public function offsetGet(mixed $offset, ReadingMode $mode = ReadingMode::Normal): mixed
    {
        $baseValue = $this->decorate->offsetGet($offset, ReadingMode::BaseValue);

        if ($mode === ReadingMode::BaseValue)
            return $baseValue;

        return Entries::valueOf($baseValue, $this, $mode);
    }

    public function getOptional($offset, ReadingMode $mode = ReadingMode::Normal): Optional
    {
        $baseOpt = $this->decorate->getOptional($offset, ReadingMode::BaseValue);

        if (! $baseOpt->isPresent() || $mode === ReadingMode::BaseValue)
            return $baseOpt;

        return Optional::of(Entries::valueOf($baseOpt->get(), $this, $mode));
    }

    public function getIterator(ReadingMode $mode = ReadingMode::Normal): \Iterator
    {
        $baseEntries = $this->decorate->getIterator(ReadingMode::BaseValue);

        if ($mode === ReadingMode::BaseValue)
            return $baseEntries;

        return Entries::entriesOf($baseEntries, $this, $mode);
    }
}