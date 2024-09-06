<?php

declare(strict_types=1);

namespace Time2Split\Config\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Time2Split\Config\_private\TreeConfigHierarchy;
use Time2Split\Config\Configurations;
use Time2Split\Config\Interpolators;

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

    public function testInterpolationFix2(): void
    {
        $a = ['text' => '${name}', 'name' => 'a'];
        $b = ['name' => 'b'];
        $a = Configurations::builder()->setInterpolator(Interpolators::recursive())->mergeTree($a)->build();
        $b = Configurations::builder()->setInterpolator(Interpolators::recursive())->mergeTree($b)->build();
        $c = Configurations::hierarchy($a, $b);

        $this->assertEquals('a', $a['text']);
        $this->assertEquals('b', $c['text']);

        $opt = $c->getOptional('text');
        $this->assertTrue($opt->isPresent());
        $this->assertEquals('b', $opt->get());

        $c['name'] = 'over';
        $this->assertEquals('over', $c['text']);
        unset($c['name']);
        $this->assertEquals('a', $c['text'], 'a2');
    }
}
