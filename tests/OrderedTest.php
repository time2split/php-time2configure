<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Time2Split\Config\Configurations;
use Time2Split\Help\IterableTrees;

final class OrderedTest extends TestCase
{
    public function testNewOrdered(): array
    {
        $config = Configurations::ofTree();
        $ordered = $config;
        $ordered = Configurations::ofTree();

        $toMerge = [
            'second.first' => 1,
            'second' => -2,
            'second.third' => 3,
            'second' => 2,
        ];
        $expect = [
            'second.first' => 1,
            'second' => 2,
            'second.third' => 3,
        ];
        $ordered->merge($toMerge);
        $this->assertSame($expect, $ordered->toArray());

        $arrayTree = [
            'second' => [
                'first' =>  1,
                'third' =>  3,
            ]
        ];
        $this->assertSame($arrayTree, $ordered->toArrayTree());

        $arrayTree = [
            'second' => [
                'first' => ['' => 1],
                '' => 2,
                'third' => ['' => 3],
            ]
        ];
        $this->assertSame($arrayTree, $ordered->toArrayTree(''));

        return [$ordered, $expect];
    }

    #[Depends("testNewOrdered")]
    public function testUnsetOrdered(array $input): void
    {
        [$ordered, $expect] = $input;
        $ordered = $ordered->copy();
        $unsetOrder = [
            'second.third',
            'second',
            'second.first',
        ];

        foreach ($unsetOrder as $k) {
            unset(
                $expect[$k],
                $ordered[$k],
            );
            $this->assertSame($expect, $ordered->toArray());
        }
        $this->assertCount(0, $ordered);
        $this->assertSame(2, IterableTrees::countLeaves($ordered->toArrayTree()));
    }

    #[Depends("testNewOrdered")]
    public function testUnsetNodeOrdered(array $input): void
    {
        [$ordered, $expect] = $input;
        $ordered = $ordered->copy();

        $k = 'second.third';
        unset($expect[$k]);
        $ordered->offsetUnsetNode($k);
        $this->assertSame($expect, $ordered->toArray());

        $k = 'second';
        $ordered->offsetUnsetNode($k);
        $this->assertCount(0, $ordered);
        $this->assertSame(0, IterableTrees::countLeaves($ordered->toArrayTree()));
    }
}
