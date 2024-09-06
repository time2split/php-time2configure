<?php

declare(strict_types=1);

namespace Time2Split\Config\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Time2Split\Config\_private\TreeConfigHierarchy;
use Time2Split\Config\Configurations;

/**
 *
 * @author Olivier Rodriguez (zuri)
 *
 */
final class TreeConfigHierarchyTest extends TestCase
{

    public function testOrderFix1(): void
    {
        $a = Configurations::ofTree(['name' => 'expect']);
        $b = Configurations::ofTree([]);

        $c = new TreeConfigHierarchy($a, $b);

        $fcheck = function () use ($c) {
            Assert::assertEquals('expect', $c['name']);
            Assert::assertEquals(['name' => 'expect'], $c->toArray());
        };

        $fcheck();
        $c['name'] = 'over';
        $this->assertEquals('over', $c['name']);
        $this->assertEquals(['name' => 'over'], $c->toArray());

        unset($c['name']);
        $fcheck();
    }
}
