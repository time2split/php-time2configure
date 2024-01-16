<?php
namespace Time2Split\Config;

use Time2Split\Help\Optional;

interface Interpolator
{

    public function compile($value): Optional;

    public function execute($value, IConfig $config): Optional;
}